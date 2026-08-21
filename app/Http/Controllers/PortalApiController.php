<?php

namespace App\Http\Controllers;

use App\Courier;
use App\LivePrice;
use App\LivePriceEvent;
use App\MillStatus;
use App\Packing;
use App\PackingType;
use App\Quality;
use App\Repositories\CourierRepository;
use App\Sample;
use App\User;
use App\ChartInterval;
use App\Port;
use App\PortImages;
use App\Role;
use App\Gallery;
use App\Contact;
use App\PaddyPrice;
use App\PaddyTrade;
use App\PaddyTradeCurrentStatus;
use App\RiceName;
use App\RiceType;
use App\RiceForm;
use App\Order;
use App\BuyQuery;
use App\Plan;
use App\SubPlan;
use App\Message;
use App\TrialPeriod;
use App\Version;
use App\OceanFreight;
use App\BagVendors;
use App\Helpers\StatusChat;
use App\USD_prices;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\FreeTrialMonths;
use App\QualityMaster;
use App\USD_defaultmaster;
use App\Defaultvalue;
use App\Vendorcategory;
use App\Bid;
use App\USDPlan;
use App\HotDealAccept;
use App\HotDealNotification;
use App\Http\Controllers\MailController;
use Illuminate\Support\Str;
use App\Notification;
use App\Brand;
use App\WandModel;
use App\WandTypeModel;
use App\SellerPackingINR;
use App\RiceFormMilestone3;
use App\SellQueriesINR;
use App\TradeQueriesINR;
use App\TradeStatusMessages;
use App\Buyerpackinginr;
use App\BuyQueriesINR;
use App\TradeLike;
use App\TradeIntrested;
use App\Mail\NewUserRegistrationAdminMail;
use App\Mail\WebTrialActivatedUserMail;
use Illuminate\Support\Facades\Mail;
use Auth;
use App\NewsRunner;
use App\TradeCurrentStatus;
use App\WebBusinessDetails;
use App\WebPersonalDetails;
use App\WebCities;
use App\WebStates;
use App\WebUserAttachment;
use App\WebPlanModel;
use App\WebPlanKeysModel;
use App\WebUserSubscriptionModel;
use App\WebUserNotification;
use App\WebAccess;
use App\VendorUserMap;
use App\WebRiceFormMap;
use App\UserInterestedMap;
use App\ServiceProviderUserMap;
use Razorpay\Api\Api;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Support\ClientPlatform;
use App\Services\PaymentInvoiceService;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

class PortalApiController extends Controller
{
    /** Maximum upload size for portal attachments (GST/PAN/farmer docs, avatar, etc.), in bytes. */
    private const PORTAL_UPLOAD_MAX_BYTES = 15 * 1024 * 1024;

    private function generateAndStoreApiToken(int $userId, string $platform = ClientPlatform::WEB): string
    {
        $column = ClientPlatform::tokenColumn($platform);

        do {
            $token = hash('sha256', Str::random(80) . microtime(true) . $userId . $platform);
        } while (
            User::where('api_token', $token)->exists()
            || User::where('mobile_api_token', $token)->exists()
        );

        User::where('id', $userId)->update([$column => $token]);

        return $token;
    }

    /**
     * @return ClientPlatform::WEB|ClientPlatform::MOBILE
     */
    private function resolveLoginPlatform(Request $request): string
    {
        return ClientPlatform::fromRequest($request);
    }

    /**
     * Store an uploaded file under public/webPortal/... (or legacy absolute paths).
     * Uses the webportal disk when possible so writes stream reliably (avoids some move() failures across tmp vs public volumes).
     *
     * @return string|false Stored filename, or false if extension is not allowed
     */
    public function uploadAttachments($file, $destination, array $requiredExtentionValidation)
    {
        if (! $file->isValid()) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                response()->json([
                    'status' => false,
                    'message' => 'Invalid upload: ' . $file->getErrorMessage(),
                ], 422)
            );
        }

        $size = $file->getSize();
        if ($size !== false && $size > self::PORTAL_UPLOAD_MAX_BYTES) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                response()->json([
                    'status' => false,
                    'message' => 'File size must not exceed 15 MB.',
                ], 422)
            );
        }

        $filename = $file->getClientOriginalName();
        $fileextension = strtolower($file->getClientOriginalExtension());
        $allowed = array_map('strtolower', $requiredExtentionValidation);

        if (! in_array($fileextension, $allowed, true)) {
            return false;
        }

        $safeFilename = $this->sanitizePortalUploadFilename($filename, $fileextension);

        $destination = $this->resolvePortalUploadDirectory($destination);
        $webPortalRoot = rtrim(str_replace('\\', '/', public_path('webPortal')), '/');
        $destNorm = rtrim(str_replace('\\', '/', $destination), '/');

        if (str_starts_with($destNorm, $webPortalRoot)) {
            $relativeDir = trim(substr($destNorm, strlen($webPortalRoot)), '/');
            if ($relativeDir === '') {
                $relativeDir = '.';
            }

            try {
                if (! File::isDirectory($webPortalRoot)) {
                    File::makeDirectory($webPortalRoot, 0775, true, true);
                }

                // putFileAs does not always create nested dirs (e.g. {userId}/attachments/gst_fssai).
                if (! File::isDirectory($destination)) {
                    File::makeDirectory($destination, 0775, true, true);
                }

                $stored = Storage::disk('webportal')->putFileAs($relativeDir, $file, $safeFilename);

                if ($stored) {
                    return basename($stored);
                }
            } catch (\Throwable $e) {
                report($e);
            }

            // Fallback: stream copy from PHP temp (works when rename/move fails across filesystems)
            try {
                $targetPath = $destination . DIRECTORY_SEPARATOR . $safeFilename;
                if (! File::isDirectory($destination)) {
                    File::makeDirectory($destination, 0775, true, true);
                }
                $src = $file->getRealPath();
                if ($src && is_readable($src) && @copy($src, $targetPath)) {
                    @chmod($targetPath, 0664);

                    return $safeFilename;
                }
            } catch (\Throwable $e) {
                report($e);
            }

            $this->throwPortalUploadFailure('Could not save the uploaded file. Ensure public/webPortal is writable by the web server (e.g. chown -R www-data:www-data public/webPortal && chmod -R 775 public/webPortal). On SELinux: chcon -R -t httpd_sys_rw_content_t public/webPortal');
        }

        // Legacy: destination outside public/webPortal
        try {
            if (! File::isDirectory($destination)) {
                File::makeDirectory($destination, 0775, true, true);
            }
            $file->move($destination, $safeFilename);

            return $safeFilename;
        } catch (\Throwable $e) {
            report($e);
            $this->throwPortalUploadFailure('Could not save the uploaded file. Check disk space and permissions on the upload folder.');
        }
    }

    private function sanitizePortalUploadFilename(string $originalName, string $extension): string
    {
        $base = pathinfo($originalName, PATHINFO_FILENAME);
        $base = preg_replace('/[^a-zA-Z0-9._\x{0900}-\x{097F}-]/u', '_', $base);
        $base = trim((string) $base, '._- ');
        if ($base === '') {
            $base = 'upload_' . Str::random(10);
        }

        return $base . '.' . $extension;
    }

    private function throwPortalUploadFailure(string $userMessage): void
    {
        $payload = [
            'status' => false,
            'message' => $userMessage,
        ];
        if (config('app.debug')) {
            $payload['debug'] = 'See laravel.log for the previous exception.';
        }

        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json($payload, 500)
        );
    }

    private function resolvePortalUploadDirectory(string $destination): string
    {
        $normalized = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $destination), DIRECTORY_SEPARATOR);

        if ($this->isAbsoluteFilesystemPath($normalized)) {
            return $normalized;
        }

        return public_path(str_replace('\\', '/', $normalized));
    }

    private function isAbsoluteFilesystemPath(string $path): bool
    {
        if ($path !== '' && ($path[0] === '/' || $path[0] === '\\')) {
            return true;
        }

        return PHP_OS_FAMILY === 'Windows'
            && strlen($path) > 2
            && ctype_alpha($path[0])
            && $path[1] === ':'
            && ($path[2] === '\\' || $path[2] === '/');
    }

    /**
     * Resolve multipart file for portal document fields.
     * Supports: documents.{field}, documents[{field}], top-level {field}, and nested maps from various clients.
     */
    private function portalDocumentsUploadedFile(Request $request, string $fieldName): ?UploadedFile
    {
        $all = $request->allFiles();

        foreach (['documents.' . $fieldName, $fieldName] as $path) {
            $f = data_get($all, $path);
            $one = $this->firstUploadedFile($f);
            if ($one !== null) {
                return $one;
            }
        }

        $documents = data_get($all, 'documents');
        if (is_array($documents)) {
            $one = $this->firstUploadedFile($documents[$fieldName] ?? null);
            if ($one !== null) {
                return $one;
            }
        }

        return $this->findNestedUploadedFileByKey($all, $fieldName, 0, 10);
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function findNestedUploadedFileByKey(array $node, string $fieldName, int $depth, int $maxDepth): ?UploadedFile
    {
        if ($depth > $maxDepth) {
            return null;
        }
        foreach ($node as $key => $value) {
            if ($key === $fieldName) {
                $one = $this->firstUploadedFile($value);
                if ($one !== null) {
                    return $one;
                }
            }
            if (is_array($value)) {
                $found = $this->findNestedUploadedFileByKey($value, $fieldName, $depth + 1, $maxDepth);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * @param  mixed  $value
     */
    private function firstUploadedFile($value): ?UploadedFile
    {
        if ($value instanceof UploadedFile) {
            return $value;
        }
        if (is_array($value)) {
            foreach ($value as $item) {
                if ($item instanceof UploadedFile) {
                    return $item;
                }
            }
        }

        return null;
    }

    /**
     * Single GST-or-FSSAI document: files are always stored under webPortal/{userId}/attachments/gst_fssai/.
     * Accepts documents[gst_fssai_file] / documents.gst_fssai_file, or legacy gst_file / fssai_file (same folder).
     * DB value: gst_fssai/{filename}.
     *
     * @return \Illuminate\Http\JsonResponse|null JSON error response, or null when nothing uploaded / success
     */
    private function applyGstFssaiDocumentUpload(Request $request, int $user_id): ?\Illuminate\Http\JsonResponse
    {
        $file = $this->portalDocumentsUploadedFile($request, 'gst_fssai_file')
            ?? $this->portalDocumentsUploadedFile($request, 'gst_file')
            ?? $this->portalDocumentsUploadedFile($request, 'fssai_file');

        if (! $file) {
            return null;
        }

        $basePath = public_path('webPortal/' . $user_id . '/attachments/gst_fssai');
        $stored = $this->uploadAttachments($file, $basePath, ['jpeg', 'jpg', 'png', 'pdf']);
        if ($stored === false) {
            return response()->json(['status' => false, 'message' => 'GST/FSSAI file must be jpeg, jpg, png, or pdf.'], 422);
        }

        WebUserAttachment::updateOrCreate(
            ['user_id' => $user_id],
            [
                'gst_fssai' => 'gst_fssai/' . $stored,
                'gstCard' => null,
                'fssaiCard' => null,
            ]
        );

        return null;
    }

    /**
     * Chunked multipart upload for PAN or GST/FSSAI documents (same storage and DB rules as updateUserDetails).
     *
     * Expected multipart fields:
     * - user_id (required): must match the authenticated user (session or Bearer / X-API-TOKEN)
     * - document_type (required): "pan" | "gst_fssai"
     * - upload_id (required): unique id per file (e.g. UUID), 8–64 chars [a-zA-Z0-9_-]
     * - chunk_index (required): 0-based index of this chunk
     * - total_chunks (required): total number of chunks for this file
     * - original_filename (required): original file name (used for extension validation)
     * - file (required): binary chunk (field name must be "file")
     *
     * When all chunks are received, they are merged, validated (jpeg/jpg/png/pdf, max 15 MB), stored under
     * public/webPortal/{userId}/attachments/{pan|gst_fssai}/, and web_user_attachment is updated.
     */
    public function uploadPortalDocumentChunk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|min:1',
            'document_type' => 'required|in:pan,gst_fssai',
            'upload_id' => ['required', 'string', 'min:8', 'max:64', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'chunk_index' => 'required|integer|min:0',
            'total_chunks' => 'required|integer|min:1|max:5000',
            'original_filename' => 'required|string|max:255',
            'file' => 'required|file|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = (int) $request->input('user_id');
        $uploadId = (string) $request->input('upload_id');
        $chunkIndex = (int) $request->input('chunk_index');
        $totalChunks = (int) $request->input('total_chunks');
        $documentType = (string) $request->input('document_type');
        $originalFilename = (string) $request->input('original_filename');

        if ($chunkIndex >= $totalChunks) {
            return response()->json([
                'status' => false,
                'message' => 'chunk_index must be less than total_chunks.',
            ], 422);
        }

        $user = User::where('id', $userId)->where('userType', 2)->first();
        if (! $user) {
            return response()->json(['status' => false, 'message' => 'User not found.'], 404);
        }

        $chunkFile = $request->file('file');
        if (! $chunkFile instanceof UploadedFile || ! $chunkFile->isValid()) {
            return response()->json(['status' => false, 'message' => 'Invalid chunk upload.'], 422);
        }

        $baseRelative = $this->portalChunkUploadRelativePath($userId, $uploadId);
        $disk = Storage::disk('local');

        $lockKey = 'portal_doc_chunk:' . $userId . ':' . $uploadId;
        $lock = Cache::lock($lockKey, 120);

        try {
            $lock->block(90);

            $disk->makeDirectory($baseRelative);
            $partName = 'part_' . sprintf('%08d', $chunkIndex);
            $chunkFile->storeAs($baseRelative, $partName, 'local');

            if (! $this->portalChunkUploadAllPartsPresent($disk, $baseRelative, $totalChunks)) {
                $received = $this->portalChunkUploadCountParts($disk, $baseRelative);

                return response()->json([
                    'status' => true,
                    'message' => 'Chunk received.',
                    'data' => [
                        'upload_id' => $uploadId,
                        'chunks_received' => $received,
                        'total_chunks' => $totalChunks,
                        'complete' => false,
                    ],
                ], 200);
            }

            $mergedPath = $this->portalChunkUploadMergeToTemp($disk, $baseRelative, $totalChunks);
            if ($mergedPath === null) {
                return response()->json(['status' => false, 'message' => 'Could not merge file chunks.'], 500);
            }

            $mergedSize = @filesize($mergedPath) ?: 0;
            if ($mergedSize > self::PORTAL_UPLOAD_MAX_BYTES) {
                $this->portalChunkUploadCleanup($disk, $baseRelative, $mergedPath);

                return response()->json([
                    'status' => false,
                    'message' => 'File size must not exceed 15 MB.',
                ], 422);
            }

            $mime = @mime_content_type($mergedPath) ?: 'application/octet-stream';
            $uploaded = new UploadedFile(
                $mergedPath,
                $originalFilename,
                is_string($mime) ? $mime : 'application/octet-stream',
                null,
                true
            );

            if ($documentType === 'pan') {
                $basePath = public_path('webPortal/' . $userId . '/attachments/pan');
                $stored = $this->uploadAttachments($uploaded, $basePath, ['jpeg', 'jpg', 'png', 'pdf']);
                if ($stored === false) {
                    $this->portalChunkUploadCleanup($disk, $baseRelative, $mergedPath);

                    return response()->json(['status' => false, 'message' => 'PAN file must be jpeg, jpg, png, or pdf.'], 422);
                }
                WebUserAttachment::updateOrCreate(['user_id' => $userId], ['panCard' => $stored]);
                $urlKey = 'pan';
            } else {
                $basePath = public_path('webPortal/' . $userId . '/attachments/gst_fssai');
                $stored = $this->uploadAttachments($uploaded, $basePath, ['jpeg', 'jpg', 'png', 'pdf']);
                if ($stored === false) {
                    $this->portalChunkUploadCleanup($disk, $baseRelative, $mergedPath);

                    return response()->json(['status' => false, 'message' => 'GST/FSSAI file must be jpeg, jpg, png, or pdf.'], 422);
                }
                WebUserAttachment::updateOrCreate(
                    ['user_id' => $userId],
                    [
                        'gst_fssai' => 'gst_fssai/' . $stored,
                        'gstCard' => null,
                        'fssaiCard' => null,
                    ]
                );
                $urlKey = 'gst_fssai';
            }

            $this->portalChunkUploadCleanup($disk, $baseRelative, $mergedPath);

            return response()->json([
                'status' => true,
                'message' => 'Upload complete.',
                'data' => [
                    'upload_id' => $uploadId,
                    'document_type' => $documentType,
                    'filename' => $stored,
                    'complete' => true,
                    'prefix' => [
                        $urlKey => 'webPortal/' . $userId . '/attachments/' . ($documentType === 'pan' ? 'pan' : 'gst_fssai'),
                    ],
                ],
            ], 200);
        } catch (HttpResponseException $e) {
            if (isset($baseRelative, $disk)) {
                $mergedCandidate = storage_path('app/' . $baseRelative . '/_merged.bin');
                $this->portalChunkUploadCleanup($disk, $baseRelative, is_file($mergedCandidate) ? $mergedCandidate : null);
            }
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            if (isset($baseRelative, $disk)) {
                $mergedCandidate = storage_path('app/' . $baseRelative . '/_merged.bin');
                $this->portalChunkUploadCleanup($disk, $baseRelative, is_file($mergedCandidate) ? $mergedCandidate : null);
            }

            return response()->json([
                'status' => false,
                'message' => 'Upload failed. Please try again.',
            ], 500);
        } finally {
            $lock->release();
        }
    }

    private function portalChunkUploadRelativePath(int $userId, string $uploadId): string
    {
        return 'portal_chunk_uploads/' . $userId . '/' . $uploadId;
    }

    private function portalChunkUploadAllPartsPresent(\Illuminate\Contracts\Filesystem\Filesystem $disk, string $baseRelative, int $totalChunks): bool
    {
        for ($i = 0; $i < $totalChunks; $i++) {
            if (! $disk->exists($baseRelative . '/part_' . sprintf('%08d', $i))) {
                return false;
            }
        }

        return true;
    }

    private function portalChunkUploadCountParts(\Illuminate\Contracts\Filesystem\Filesystem $disk, string $baseRelative): int
    {
        $files = $disk->files($baseRelative);
        $n = 0;
        foreach ($files as $path) {
            $name = basename($path);
            if (preg_match('/^part_\d{8}$/', $name)) {
                $n++;
            }
        }

        return $n;
    }

    /**
     * @return string|null Absolute path to merged temp file
     */
    private function portalChunkUploadMergeToTemp(\Illuminate\Contracts\Filesystem\Filesystem $disk, string $baseRelative, int $totalChunks): ?string
    {
        $mergedRelative = $baseRelative . '/_merged.bin';
        $mergedPath = storage_path('app/' . $mergedRelative);
        $out = @fopen($mergedPath, 'wb');
        if (! $out) {
            return null;
        }

        try {
            for ($i = 0; $i < $totalChunks; $i++) {
                $partRelative = $baseRelative . '/part_' . sprintf('%08d', $i);
                if (! $disk->exists($partRelative)) {
                    @unlink($mergedPath);

                    return null;
                }
                $partPath = storage_path('app/' . $partRelative);
                $in = @fopen($partPath, 'rb');
                if (! $in) {
                    @unlink($mergedPath);

                    return null;
                }
                stream_copy_to_stream($in, $out);
                fclose($in);
            }
        } finally {
            fclose($out);
        }

        return is_file($mergedPath) ? $mergedPath : null;
    }

    private function portalChunkUploadCleanup(\Illuminate\Contracts\Filesystem\Filesystem $disk, string $baseRelative, ?string $mergedAbsolutePath): void
    {
        if ($mergedAbsolutePath && is_file($mergedAbsolutePath)) {
            @unlink($mergedAbsolutePath);
        }
        try {
            $disk->deleteDirectory($baseRelative);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function loginUser(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'mobile' => 'required|regex:/^[0-9]{10}$/'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }
        if ($request->has('mobile') ) {
            $mobile = $request->mobile;
            // ✅ Ensure we're finding/creating the correct user with proper scoping
            $user = User::where(['mobile' => $mobile, 'userType' => 2, 'user_from' => 'web'])->first();
            $Newotp = rand(1000, 9999);

            if (!$user) {
                // ✅ Create new user if not found
                $user = User::create(['mobile' => $mobile, 'otp' => $Newotp, 'userType' => 2, 'user_from' => 'web']);
            } else {
                // ✅ Update OTP for existing user (only updates the specific user found)
                $user->update(['otp' => $Newotp]);
            }

            $message = "SNTC rice sourcing OTP is $Newotp. Do not share this with anyone. - SNTC AGRO TECHNOLOGY";

            $url = "http://www.truebulksms.biz/api.php?username=rijulbajaj&password=158190&sender=SNTCAL&sendto="
                  . $mobile
                  . "&message=" . urlencode($message)
                  . "&PEID=1701172916686910712&templateid=1707176544745633588";
            if ($user) {
                file_get_contents($url);
                $userResponse = $user->toArray();
                unset($userResponse['otp']);
                return response()->json([
                    'status' => true,
                    'message' => 'OTP sent successfully',
                    'data' => $userResponse
                ], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'Wrong user credentials'], 401);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Required fields are missing'], 401);
        }
    }

    public function verifyOTPAndLogin(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'mobile' => 'required|regex:/^[0-9]{10}$/',
            'otp' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }
        if ($request->has('mobile')) {
            $mobile = $request->mobile;
            $otp = $request->otp;
            $user = User::where(['mobile' => $mobile, 'otp' => $otp, 'userType' => 2])->first();

            if ($user) {
                if ($blocked = $this->portalAccessBlockedResponse($user)) {
                    return $blocked;
                }
                $token = $this->generateAndStoreApiToken((int) $user->id, $this->resolveLoginPlatform($request));
                // ✅ Create Laravel session using 'web' guard (sets httpOnly cookie automatically)
                auth('web')->login($user);
                
                // ✅ Ensure session is saved so Set-Cookie header is sent
                $request->session()->save();
                
                // ✅ Reload user with relationships using the user ID to ensure we get the correct user
                $data = User::where('id', $user->id)->where('userType', 2)->with(array_merge(
                    $this->portalPersonalDetailsWithRelations(),
                    [
                        'getWebBusinessDetails' => function ($q) {
                            return $q->with(['getCategoryDetails:id,category']);
                        },
                        'getWebUserAttachment',
                        'getWebUserSubscription' => function ($q) {
                            return $q->whereDate('period_end', '>=', Carbon::now()->format('Y-m-d'));
                        },
                        'role_rel',
                    ]
                ))->first();
                
                // Check if user data was found
                if (!$data) {
                    return response()->json(['status' => false, 'message' => 'User not found'], 404);
                }

                $this->hydratePersonalLocationRelations($data->getWebPersonalDetails);

                $hasActivePlan = false;
                if($data->getWebUserSubscription){
                    $hasActivePlan = true;
                }
                
                $hasBasicDetails = $this->portalUserHasBasicProfileDetails($data);
                $hasUploadedDocuments = $this->portalUserHasUploadedDocuments($data);

                $checkIfTrailDone = WebUserSubscriptionModel::where('user_id', $user->id)->where('subscription_type', 'trial')->first();
                
                $hasTrialDone = false;
                if($checkIfTrailDone){
                    $hasTrialDone = true;
                }

                // ✅ Return response with session cookie
                return response()->json([
                    'status' => true, 
                    'message' => 'Success', 
                    'token' => $token,
                    'platform' => $this->resolveLoginPlatform($request),
                    'hasBasicDetails' => $hasBasicDetails,
                    'hasUploadedDocuments' => $hasUploadedDocuments,
                    'hasTrialDone' => $hasTrialDone,
                    'hasActivePlan' => $hasActivePlan,
                    'total_available_days' => $this->getTotalAvailableSubscriptionDays((int) $user->id),
                    'planDetails' => $data->getWebUserSubscription, 
                    'data' => $data
                ], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'Wrong user credentials'], 401);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Required fields are missing'], 401);
        }
    }

    public function saveUser(Request $request)
    {
        $mobile = $request['mobile'];
        $Newotp = rand(1000, 9999);
        $user = User::where(['mobile' => $mobile, 'userType' => 2,'user_from' => 'web'])->first();
        if (!$user) {
            $user = User::create(['mobile' => $mobile, 'otp' => $Newotp, 'userType' => 2,'user_from' => 'web']);
        } else {
            $user->update(['otp' => $Newotp]);
        }

        $message = "SNTC rice sourcing OTP is $Newotp. Do not share this with anyone. - SNTC AGRO TECHNOLOGY";
        $url = "http://www.truebulksms.biz/api.php?username=rijulbajaj&password=158190&sender=SNTCAL&sendto="
              . $mobile
              . "&message=" . urlencode($message)
              . "&PEID=1701172916686910712&templateid=1707176544745633588";
        file_get_contents($url);

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully on ' . $mobile,
            'data' => ['user_id' => $user->id],
            'isVerified' => ($user->is_INR_active == 1) ? true : false
        ], 200);
    }

    public function verifyOTP(Request $request)
    {
        if ($request->has('user_id') && $request->has('otp') && $request->user_id != '' && $request->otp != '') {
            $user_id = $request->user_id;
            $otp = $request->otp;
            $user = User::where(['id' => $user_id, 'otp' => $otp, 'userType' => 2])->first();

            if ($user) {
                if ($blocked = $this->portalAccessBlockedResponse($user)) {
                    return $blocked;
                }
                $user->update(['is_INR_active' => 1]);
                $token = $this->generateAndStoreApiToken((int) $user->id, $this->resolveLoginPlatform($request));
                
                // ✅ Create session after OTP verification using 'web' guard
                auth('web')->login($user);
                
                // ✅ Ensure session is saved so Set-Cookie header is sent
                $request->session()->save();

                // ✅ Reload user with relationships using the user ID to ensure we get the correct user
                $data = User::where('id', $user->id)->where('userType', 2)->with(array_merge(
                    $this->portalPersonalDetailsWithRelations(),
                    [
                        'getWebBusinessDetails' => function ($q) {
                            return $q->with(['getCategoryDetails:id,category']);
                        },
                        'getWebUserAttachment',
                        'getWebUserSubscription' => function ($q) {
                            return $q->whereDate('period_end', '>=', Carbon::now()->format('Y-m-d'));
                        },
                        'role_rel',
                    ]
                ))->first();
                
                // Check if user data was found
                if (!$data) {
                    return response()->json(['status' => false, 'message' => 'User not found'], 404);
                }

                $this->hydratePersonalLocationRelations($data->getWebPersonalDetails);
                
                $hasActivePlan = false;
                if($data->getWebUserSubscription){
                    $hasActivePlan = true;
                }
                
                $hasBasicDetails = $this->portalUserHasBasicProfileDetails($data);
                $hasUploadedDocuments = $this->portalUserHasUploadedDocuments($data);

                $checkIfTrailDone = WebUserSubscriptionModel::where('user_id', $user->id)->where('subscription_type', 'trial')->first();
                
                $hasTrialDone = false;
                if($checkIfTrailDone){
                    $hasTrialDone = true;
                }

                // ✅ Return response with session cookie
                return response()->json([
                    'status' => true, 
                    'message' => 'OTP verified successfully', 
                    'token' => $token,
                    'platform' => $this->resolveLoginPlatform($request),
                    'hasBasicDetails' => $hasBasicDetails,
                    'hasUploadedDocuments' => $hasUploadedDocuments,
                    'hasActivePlan' => $hasActivePlan,
                    'hasTrialDone' => $hasTrialDone,
                    'total_available_days' => $this->getTotalAvailableSubscriptionDays((int) $user->id),
                    'planDetails' => $data->getWebUserSubscription, 
                    'data' => $data
                ], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'Wrong OTP'], 401);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Required fields are missing'], 401);
        }
    }
    
    public function resendOTP(Request $request)
    {
        if ($request->has('user_id')) {
            $otp = round(1000, 9999);
            $user_id = $request->user_id;

            $user = User::where(['id' => $user_id]);
            if ($user->first()) {
                $user->update(['otp' => $otp]);
                $mobile = ($user->first()->mobile);

                $message = "SNTC rice sourcing OTP is $otp. Do not share this with anyone. - SNTC AGRO TECHNOLOGY";

                $url = "http://www.truebulksms.biz/api.php?username=rijulbajaj&password=158190&sender=SNTCAL&sendto="
                      . $mobile
                      . "&message=" . urlencode($message)
                      . "&PEID=1701172916686910712&templateid=1707176544745633588";
                file_get_contents($url);

                // file_get_contents('http://www.truebulksms.biz/api.php?username=rijulbajaj&password=158190&sender=SNTCAL&sendto=' . $mobile . '&message=Thank+you+for+registering+on+SNTC+Rice+Live+Pricing+App.+Your+OTP+Code+is+' . $otp . '.+SNTCAL&PEID=1701172916686910712&templateid=1707172924575773908');

                return response()->json(['status' => true, 'message' =>  'OTP send successfully'], 200);
            } else {
                return response()->json(['status' => false, 'message' => $mobile . ' not available in records'], 401);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Required fields are missing'], 401);
            //error : required fields are missing
        }
    }

    public function updateUserDetails(Request $request)
    {
        $user_id = (int) $request->input('user_id');
        if ($user_id < 1) {
            return response()->json(['status' => false, 'message' => 'Valid user_id is required.'], 422);
        }

        $personalDetails = [];
        $businessDetails = [];
        $userEmailForMail = '';

        $rawPersonal = $request->input('personal_details', []);
        if (! is_array($rawPersonal)) {
            $rawPersonal = [];
        }
        $rawBusiness = $request->input('business_details', []);
        if (! is_array($rawBusiness)) {
            $rawBusiness = [];
        }

        // Farmer registration historically sent UID/PAN/district under business_details,
        // but those columns live on web_personal_details.
        $personalDetails = $this->normalizePortalPersonalDetailsPayload($rawPersonal, $rawBusiness);
        $hasPersonalPayload = $personalDetails !== [];
        $avatarFile = $request->file('personal_details.avatar')
            ?? $request->file('personal_details')['avatar'] ?? null;

        if ($hasPersonalPayload || $avatarFile instanceof UploadedFile) {
            $firstname = trim((string) ($personalDetails['firstname'] ?? ''));
            $lastname = trim((string) ($personalDetails['lastname'] ?? ''));
            $email = trim((string) ($personalDetails['email'] ?? ''));
            $userEmailForMail = $email;

            // Prevent duplicate web-user email during basic details save.
            if ($email !== '') {
                $emailAlreadyUsed = User::where('email', $email)
                    ->where('user_from', 'web')
                    ->where('id', '!=', $user_id)
                    ->exists();

                if ($emailAlreadyUsed) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Email is already in use , please use different email or login if you already have account.'
                    ], 422);
                }
            }

            $userUpdate = [];
            $fullName = trim($firstname . ' ' . $lastname);
            if ($fullName !== '') {
                $userUpdate['name'] = $fullName;
            }
            if ($email !== '') {
                $userUpdate['email'] = $email;
            }
            if ($request->filled('role')) {
                $userUpdate['role'] = $request->input('role');
            }
            if ($userUpdate !== []) {
                User::where('id', $user_id)->update($userUpdate);
            }

            if ($avatarFile instanceof UploadedFile) {
                $basePath = public_path('webPortal/' . $user_id . '/attachments/avatar');
                $file = $this->uploadAttachments($avatarFile, $basePath, ['jpeg', 'jpg', 'png']);
                if ($file === false) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Avatar must be jpeg, jpg, or png.',
                    ], 422);
                }
                $personalDetails['avatar'] = $file;
            } elseif (array_key_exists('avatar', $personalDetails)
                && $personalDetails['avatar'] instanceof UploadedFile) {
                $basePath = public_path('webPortal/' . $user_id . '/attachments/avatar');
                $file = $this->uploadAttachments($personalDetails['avatar'], $basePath, ['jpeg', 'jpg', 'png']);
                if ($file === false) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Avatar must be jpeg, jpg, or png.',
                    ], 422);
                }
                $personalDetails['avatar'] = $file;
            } else {
                unset($personalDetails['avatar']);
            }

            $personalDetails['user_id'] = $user_id;
            $personalFillable = array_flip((new WebPersonalDetails)->getFillable());
            $personalToSave = array_intersect_key($personalDetails, $personalFillable);
            $personalToSave['user_id'] = $user_id;

            WebPersonalDetails::updateOrCreate(['user_id' => $user_id], $personalToSave);
            $personalDetails = $personalToSave;
        }

        if ($rawBusiness !== []) {
            $businessDetails = $this->normalizePortalBusinessDetailsPayload($rawBusiness);
            $businessDetails['user_id'] = $user_id;
            $businessFillable = array_flip((new WebBusinessDetails)->getFillable());
            $businessToSave = array_intersect_key($businessDetails, $businessFillable);
            $businessToSave['user_id'] = $user_id;

            WebBusinessDetails::updateOrCreate(['user_id' => $user_id], $businessToSave);
            $businessDetails = $businessToSave;

            if( $request->has('role') && $request->role == 11 ) {
                $vendorDetails = [
                    'type' => $request['vendorDetails']['type']??'--',
                    'key' => $request['vendorDetails']['key']??'--',
                    'value' => $request['vendorDetails']['value']??'--',
                    'remarks' => $request['vendorDetails']['remarks']??'--',
                    'status' => 1
                ];
                $this->upsertVendorUserMap($user_id, $vendorDetails);
            }

            if( $request->has('role') && $request->role == 12 ) { 
               $serviceProviderDetails = [
                    'type' => $request['serviceProviderDetails']['type']??'--',
                    'key' => $request['serviceProviderDetails']['key']??'--',
                    'value' => $request['serviceProviderDetails']['value']??'--',
                    'remarks' => $request['serviceProviderDetails']['remarks']??'--',
                    'status' => 1
                ];
                $this->upsertServiceProviderUserMap($user_id, $serviceProviderDetails);
            }
        }

        if (($panFile = $this->portalDocumentsUploadedFile($request, 'pan_file')) === null) {
            $panFile = $this->portalDocumentsUploadedFile($request, 'pancard_file');
        }
        if ($panFile !== null) {
            $basePath = public_path('webPortal/' . $user_id . '/attachments/pan');
            $file = $this->uploadAttachments($panFile, $basePath, ['jpeg', 'jpg', 'png', 'pdf']);
            if ($file === false) {
                return response()->json(['status' => false, 'message' => 'PAN file must be jpeg, jpg, png, or pdf.'], 422);
            }
            WebUserAttachment::updateOrCreate(['user_id' => $user_id], ['panCard' => $file]);
        }

        if (($farmerFile = $this->portalDocumentsUploadedFile($request, 'farmer_file')) === null) {
            $farmerFile = $this->portalDocumentsUploadedFile($request, 'uid_file');
        }
        if ($farmerFile !== null) {
            $basePath = public_path('webPortal/' . $user_id . '/attachments/farmer_file');
            $file = $this->uploadAttachments($farmerFile, $basePath, ['jpeg', 'jpg', 'png', 'pdf']);
            if ($file === false) {
                return response()->json(['status' => false, 'message' => 'Farmer document must be jpeg, jpg, png, or pdf.'], 422);
            }
            WebUserAttachment::updateOrCreate(['user_id' => $user_id], ['farmer_file' => $file]);
        }

        $gstFssaiError = $this->applyGstFssaiDocumentUpload($request, $user_id);
        if ($gstFssaiError !== null) {
            return $gstFssaiError;
        }


        $accountType = strtolower(trim((string) $request->input('account_type', '')));
        $mailTo = 'info@sntcgroup.com';
        $mailMessage = '';
        $mailFrom = 'info@sntcgroup.com';
        $mailFromName = 'SNTC Team - India';
        $mailUser = User::query()->where('id', $user_id)->first(['email']);
        $mailUserEmail = trim((string) ($mailUser->email ?? $userEmailForMail));

        // Admin registration mail ("New User Registration-Webversion") is sent from activateTrialSubscription()
        // via NewUserRegistrationAdminMail — do not duplicate here when account_type=new.
        // account_type != new -> profile update notification only.
        if ($mailUserEmail !== '' && $accountType !== 'new') {
            $subject = 'User update the profile';
            $data = ['userEmail' => $mailUserEmail];
            Mail::send('mail.userUpdateProfile', $data, function ($message) use ($mailTo, $mailMessage, $subject, $mailFrom, $mailFromName) {
                $message->to($mailTo, $mailMessage)->subject($subject);
                $message->from($mailFrom, $mailFromName);
            });
        }


        return response()->json(['status' => true, 'message' => 'user details added successfully', 'data' => ['personalDetails' => $personalDetails, 'businessDetails' => $businessDetails]], 200);
    }

    /**
     * Map personal_details (+ legacy farmer fields from business_details) onto web_personal_details columns.
     *
     * @param  array<string, mixed>  $personal
     * @param  array<string, mixed>  $business
     * @return array<string, mixed>
     */
    private function normalizePortalPersonalDetailsPayload(array $personal, array $business): array
    {
        $out = [];

        foreach ([
            'firstname',
            'lastname',
            'email',
            'phone_number',
            'state',
            'district',
            'address',
            'farmer_unique_id',
            'pan_card',
            'status',
        ] as $key) {
            if (array_key_exists($key, $personal) && $personal[$key] !== null && $personal[$key] !== '') {
                $out[$key] = is_string($personal[$key]) ? trim($personal[$key]) : $personal[$key];
            }
        }

        // Legacy farmer registration put these under business_details.
        if (! array_key_exists('farmer_unique_id', $out)) {
            $uid = $business['farmer_uid'] ?? $business['farmer_unique_id'] ?? null;
            if ($uid !== null && $uid !== '') {
                $out['farmer_unique_id'] = is_string($uid) ? trim($uid) : $uid;
            }
        }
        if (! array_key_exists('pan_card', $out)) {
            $pan = $business['farmer_pancard'] ?? $business['pan_card'] ?? null;
            if ($pan !== null && $pan !== '') {
                $out['pan_card'] = is_string($pan) ? trim($pan) : $pan;
            }
        }
        if (! array_key_exists('district', $out) && ! empty($business['district'])) {
            $out['district'] = is_string($business['district']) ? trim($business['district']) : $business['district'];
        }
        if (! array_key_exists('state', $out) && ! empty($business['state'])
            && empty($business['company_name']) && empty($business['city'])) {
            // Farmer forms often send state only under business_details.
            $out['state'] = is_string($business['state']) ? trim($business['state']) : $business['state'];
        }

        return $out;
    }

    /**
     * Keep only business table fields; map registered_email aliases.
     *
     * @param  array<string, mixed>  $business
     * @return array<string, mixed>
     */
    private function normalizePortalBusinessDetailsPayload(array $business): array
    {
        $out = $business;

        if (! empty($business['email']) && empty($business['registered_email'])) {
            $out['registered_email'] = is_string($business['email']) ? trim($business['email']) : $business['email'];
        }

        // Not columns on web_business_details — moved to personal details.
        unset(
            $out['farmer_uid'],
            $out['farmer_unique_id'],
            $out['farmer_pancard'],
            $out['pan_card'],
            $out['district'],
            $out['email'],
            $out['pincode']
        );

        return $out;
    }

    /**
     * Block portal login/session when admin has deactivated or rejected the user.
     */
    private function portalAccessBlockedResponse(User $user): ?\Illuminate\Http\JsonResponse
    {
        if ($blockedMessage = $user->authAccessBlockedMessage()) {
            return response()->json([
                'status' => false,
                'message' => $blockedMessage,
            ], 403);
        }

        return null;
    }

    /**
     * Dynamic has_validation for portal when admin has not activated the user yet.
     */
    private function resolvePortalHasValidationMessage(User $user, ?WebUserAttachment $attachment): string
    {
        if ((int) ($user->is_active_by_admin ?? 0) !== 0) {
            return trim((string) ($user->has_validation ?? ''));
        }

        if ($attachment !== null && $attachment->hasAnyTrialDocumentUploaded()) {
            return 'Document verification in process.';
        }

        return 'Please submit your documents to complete your profile.';
    }

    /**
     * Keep a single vendor_user_map row per user (update latest, remove duplicates).
     *
     * @param  array<string, mixed>  $attributes
     */
    private function upsertVendorUserMap(int $userId, array $attributes): void
    {
        $payload = array_merge($attributes, [
            'user_id' => $userId,
            'status' => (int) ($attributes['status'] ?? 1),
        ]);

        $existing = VendorUserMap::query()
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->first();

        if ($existing) {
            $existing->update($payload);
            VendorUserMap::query()
                ->where('user_id', $userId)
                ->where('id', '!=', $existing->id)
                ->delete();
        } else {
            VendorUserMap::create($payload);
        }
    }

    /**
     * Keep a single service_provider_user_map row per user (update latest, remove duplicates).
     *
     * @param  array<string, mixed>  $attributes
     */
    private function upsertServiceProviderUserMap(int $userId, array $attributes): void
    {
        $payload = array_merge($attributes, [
            'user_id' => $userId,
            'status' => (int) ($attributes['status'] ?? 1),
        ]);

        $existing = ServiceProviderUserMap::query()
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->first();

        if ($existing) {
            $existing->update($payload);
            ServiceProviderUserMap::query()
                ->where('user_id', $userId)
                ->where('id', '!=', $existing->id)
                ->delete();
        } else {
            ServiceProviderUserMap::create($payload);
        }
    }

    /**
     * Vendor / service-provider profile fields saved during portal registration (role 11 or 12).
     *
     * @return array<string, string|null>|null
     */
    private function resolvePortalVendorDetailsForUser(User $user): ?array
    {
        $role = (int) ($user->role ?? 0);
        $userId = (int) $user->id;

        if ($userId < 1) {
            return null;
        }

        $record = null;
        if ($role === 11) {
            $record = VendorUserMap::query()
                ->where('user_id', $userId)
                ->where('status', 1)
                ->orderByDesc('id')
                ->first(['type', 'key', 'value', 'remarks']);
        } elseif ($role === 12) {
            $record = ServiceProviderUserMap::query()
                ->where('user_id', $userId)
                ->where('status', 1)
                ->orderByDesc('id')
                ->first(['type', 'key', 'value', 'remarks']);
        }

        if ($record === null) {
            return null;
        }

        return [
            'vendor_type' => $record->type,
            'packing_type' => $record->key,
            'specialisation' => $record->value,
            'remarks' => $record->remarks,
        ];
    }

    /**
     * Expose product and business contact fields on the portal user payload.
     *
     * @param  array<string, mixed>  $user
     * @return array<string, mixed>
     */
    private function appendWebBusinessContactFields(array $user): array
    {
        $business = $user['get_web_business_details'] ?? null;
        if (! is_array($business)) {
            $user['product'] = null;
            $user['contact_name'] = null;
            $user['contact_phone'] = null;

            return $user;
        }

        $product = $business['product'] ?? null;
        $contactName = $business['contactPerson'] ?? ($business['contact_name'] ?? null);
        $contactPhone = $business['contactMobile'] ?? ($business['contact_phone'] ?? null);

        $user['get_web_business_details']['contact_name'] = $contactName;
        $user['get_web_business_details']['contact_phone'] = $contactPhone;
        $user['product'] = $product;
        $user['contact_name'] = $contactName;
        $user['contact_phone'] = $contactPhone;

        return $user;
    }

    /**
     * Personal details with state/district master relations.
     */
    private function portalPersonalDetailsWithRelations(): array
    {
        return [
            'getWebPersonalDetails' => function ($q) {
                $q->with([
                    'stateRel:id,state_name,state_code',
                    'districtRel:id,city_name,state_id',
                ]);
            },
        ];
    }

    /**
     * When state/district were saved as names (legacy farmer form), attach masters by name.
     */
    private function hydratePersonalLocationRelations(?WebPersonalDetails $personal): void
    {
        if ($personal === null) {
            return;
        }

        if ($personal->stateRel === null && filled($personal->state) && ! is_numeric($personal->state)) {
            $personal->setRelation(
                'stateRel',
                WebStates::query()
                    ->where('state_name', $personal->state)
                    ->first(['id', 'state_name', 'state_code'])
            );
        }

        if ($personal->districtRel === null && filled($personal->district) && ! is_numeric($personal->district)) {
            $cityQuery = WebCities::query()->where('city_name', $personal->district);
            if ($personal->stateRel && (int) $personal->stateRel->id > 0) {
                $cityQuery->where('state_id', (int) $personal->stateRel->id);
            } elseif (is_numeric($personal->state)) {
                $cityQuery->where('state_id', (int) $personal->state);
            }
            $personal->setRelation(
                'districtRel',
                $cityQuery->first(['id', 'city_name', 'state_id'])
            );
        }
    }

    public function getUserDetails($userId)
    {
        if ($userId != null) {
            $userModel = User::where('id', $userId)->where('userType', 2)->with(array_merge(
                $this->portalPersonalDetailsWithRelations(),
                [
                    'getWebBusinessDetails' => function ($q) {
                        return $q->with(['cityRel:id,city_name', 'stateRel:id,state_name', 'getCategoryDetails:id,category', 'getBagVendorWeb:id,category']);
                    },
                    'getWebUserAttachment',
                    'getWebUserSubscription.planRel',
                    'role_rel',
                ]
            ))->first();

            if (!$userModel) {
                return response()->json(['status' => false, 'message' => 'User not found', 'data' => []], 404);
            }

            $this->hydratePersonalLocationRelations($userModel->getWebPersonalDetails);

            $user = $userModel->toArray();
            $user['has_validation'] = $this->resolvePortalHasValidationMessage(
                $userModel,
                $userModel->getWebUserAttachment
            );
            unset($user['otp']);

            // if( $user['role'] == 12 ){
            //     $user['get_web_business_details']['get_category_details'] =  $user['get_web_business_details']['get_bag_vendor_web'];
            // }
            if (isset($user['get_web_business_details']['get_bag_vendor_web'])) {
                unset($user['get_web_business_details']['get_bag_vendor_web']);
            }

            $vendorDetails = $this->resolvePortalVendorDetailsForUser($userModel);
            if (in_array((int) $userModel->role, [11, 12], true)) {
                $user['vendor_details'] = $vendorDetails ?? [
                    'vendor_type' => null,
                    'packing_type' => null,
                    'specialisation' => null,
                    'remarks' => null,
                ];
            }

            $user = $this->appendWebBusinessContactFields($user);

            return response()->json(['status' => true, 'message' => 'user details added successfully', 'data' => $user, 'prefix' => [
                'avatar' => 'webPortal/' . $userId . '/attachments/avatar',
                'pan' => 'webPortal/' . $userId . '/attachments/pan',
                'attachments' => 'webPortal/' . $userId . '/attachments',
                'gst_fssai' => 'webPortal/' . $userId . '/attachments/gst_fssai',
            ], 'total_available_days' => $this->getTotalAvailableSubscriptionDays((int) $userId)], 200);


        } else {
            return response()->json(['status' => false, 'message' => 'required field is missing', 'data' => []], 401);
        }
    }

    public function deleteUser($userId)
    {
        if ($userId != null) {
            $user = User::where('id', $userId)->where('userType', 2)->delete();
            return response()->json(['status' => true, 'message' => 'user deleted successfully', 'data' => $user], 200);
        } else {
            return response()->json(['status' => false, 'message' => 'userid required', 'data' => []], 401);
        }
    }

    public function getPlans()
    {
        $webKeys = WebPlanKeysModel::select(["id","key","status","order_no"])
            ->where(['status'  =>  1])
            ->orderByRaw('order_no IS NULL, order_no ASC')
            ->orderBy('id')
            ->get()
            ->pluck('key','id');
        $activeWebKeyIds = $webKeys->keys()->map(fn($id) => (int) $id)->toArray();

        $plans = WebPlanModel::select([
                "id","title","short_description","description","status",
                "monthly_price","quarterly_price","yearly_price",
                "monthly_discount_percentage","quarterly_discount_percentage","yearly_discount_percentage",
                "monthly_final_amount","quarterly_final_amount","yearly_final_amount"
            ])
            ->where('title' ,'!=' , '')
            ->with(['getPlanKeyMap:key_id,plan_id'])
            ->where(['status' => 1])
            ->get()
            ->map(function ($q) use ($activeWebKeyIds) {
                return [
                    'plan' => [
                        'id' => $q->id,
                        'title' => $q->title,
                        'short_description' => $q->short_description,
                        'description' => $q->description,
                        'status' => $q->status
                    ],
                    'pricing' => [
                        'monthly' => [
                            'price' => $q->monthly_price,
                            'discount_percentage' => $q->monthly_discount_percentage,
                            'final_amount' => $q->monthly_final_amount
                        ],
                        'quarterly' => [
                            'price' => $q->quarterly_price,
                            'discount_percentage' => $q->quarterly_discount_percentage,
                            'final_amount' => $q->quarterly_final_amount
                        ],
                        'yearly' => [
                            'price' => $q->yearly_price,
                            'discount_percentage' => $q->yearly_discount_percentage,
                            'final_amount' => $q->yearly_final_amount
                        ],
                    ],
                    'availableKeys' => $q->getPlanKeyMap
                        ->pluck('key_id')
                        ->map(fn($id) => (int) $id)
                        ->filter(fn($id) => in_array($id, $activeWebKeyIds))
                        ->values()
                ];
            })->toArray();
        return response()->json(['status' => true, 'message' => 'Web Plans', 'data' => ['plans' => $plans , 'webKeys' => $webKeys]], 200);
    }

    public function webUserSubscription(Request $request)
    {
        $validate = $request->validate([
            'user_id' => 'required',
            'plan_id' => 'required',
            'subscription_type' => 'required',
        ]);
        $data = [
            'user_id' => $request->user_id,
            'plan_id' => $request->plan_id,
            'subscription_type' => $request->subscription_type,
            'period_start' =>  Carbon::now()->format('Y-m-d'),
            'period_end' => Carbon::now()->addDays(7)->format('Y-m-d')
        ];
        WebUserSubscriptionModel::create($data);
        return response()->json(['status' => true, 'message' => 'User subscription added successfully', 'data' => $data], 200);

    }

    /**
     * Create a renewal entry in web_user_subscription.
     * If a user still has an active plan, renewed plan starts from next day of period_end.
     * Otherwise it starts from today.
     */
    public function webRenewSubscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'plan_id' => 'required|integer|exists:web_plan,id',
            'subscription_type' => 'required|string|in:trial,monthly,half_yearly,yearly',
            'payment_id' => 'required|string|max:255',
            'order_id' => 'required|string|max:255',
            'status' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = (int) $request->input('user_id');
        $planId = (int) $request->input('plan_id');
        $subscriptionType = (string) $request->input('subscription_type');
        $paymentId = (string) $request->input('payment_id');
        $orderId = (string) $request->input('order_id');
        $rowStatus = $request->filled('status') ? (int) $request->input('status') : 1;

        $alreadyExists = WebUserSubscriptionModel::where(function ($q) use ($paymentId, $orderId) {
            $q->where('payment_id', $paymentId)->orWhere('order_id', $orderId);
        })->exists();
        if ($alreadyExists) {
            return response()->json([
                'status' => false,
                'message' => 'Subscription with same payment_id or order_id already exists.',
            ], 422);
        }

        $renewalStart = $this->getNextSubscriptionStartDate($userId);

        $addedDays = $this->getSubscriptionAddedDays($subscriptionType);
        $renewalEnd = (clone $renewalStart)->addDays($addedDays);

        $subscription = WebUserSubscriptionModel::create([
            'user_id' => $userId,
            'plan_id' => $planId,
            'payment_id' => $paymentId,
            'order_id' => $orderId,
            'subscription_type' => $subscriptionType,
            'period_start' => $renewalStart->format('Y-m-d'),
            'period_end' => $renewalEnd->format('Y-m-d'),
            'status' => $rowStatus,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Plan renewed successfully.',
            'data' => $subscription,
        ], 200);
    }

    private function planFinalAmount(?WebPlanModel $plan, string $subscriptionType): float
    {
        if ($plan === null) {
            return 0.0;
        }
        if ($subscriptionType === 'yearly') {
            return (float) ($plan->yearly_final_amount ?? $plan->yearly_price ?? 0);
        }
        if (in_array($subscriptionType, ['quarterly', 'half_yearly'], true)) {
            return (float) ($plan->quarterly_final_amount ?? $plan->quarterly_price ?? 0);
        }

        return (float) ($plan->monthly_final_amount ?? $plan->monthly_price ?? 0);
    }

    private function getSubscriptionAddedDays(string $subscriptionType): int
    {
        if ($subscriptionType === 'trial') {
            return 30;
        }
        if ($subscriptionType === 'monthly') {
            return 30;
        }
        if ($subscriptionType === 'half_yearly') {
            return 183;
        }
        if ($subscriptionType === 'yearly') {
            return 365;
        }

        return 7;
    }

    public function getWebPlans()
    {
        $role = Role::select(["id","role_name"])->where('type' , 'web')->get();
        return response()->json(['status' => true, 'message' => 'Role get successfully', 'data' => $role], 200);
    }

    /**
     * Public endpoint for website: list live price events.
     *
     * Optional query params:
     * - quality_type_id
     * - quality_id
     * - quality_form_id
     * - from_date (Y-m-d)
     * - to_date (Y-m-d)
     * - limit (default 100, max 500)
     */
    public function getLivePriceEvents(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quality_type_id' => 'nullable|integer|exists:rice_types,id',
            'quality_id' => 'nullable|integer|exists:rice_names,id',
            'quality_form_id' => 'nullable|integer|exists:rice_forms,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'limit' => 'nullable|integer|min:1|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $limit = (int) ($request->input('limit', 100));

        $query = LivePriceEvent::query()
            ->with(['qualityType:id,name', 'quality:id,name,type', 'qualityForm:id,form_name,type'])
            ->where('status', 1)
            ->orderBy('event_date', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('quality_type_id')) {
            $query->where('quality_type_id', (int) $request->quality_type_id);
        }
        if ($request->filled('quality_id')) {
            $query->where('quality_id', (int) $request->quality_id);
        }
        if ($request->filled('quality_form_id')) {
            $query->where('quality_form_id', (int) $request->quality_form_id);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('event_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('event_date', '<=', $request->to_date);
        }

        $events = $query->limit($limit)->get()->map(function ($row) {
            return [
                'id' => $row->id,
                'title' => $row->title,
                'event_date' => $row->event_date,
                'note' => $row->note,
                'quality_type' => $row->qualityType ? [
                    'id' => $row->qualityType->id,
                    'name' => $row->qualityType->name,
                ] : null,
                'quality' => $row->quality ? [
                    'id' => $row->quality->id,
                    'name' => $row->quality->name,
                    'type' => $row->quality->type,
                ] : null,
                'quality_form' => $row->qualityForm ? [
                    'id' => $row->qualityForm->id,
                    'name' => $row->qualityForm->form_name,
                    'type' => $row->qualityForm->type,
                ] : null,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Live price events list',
            'data' => $events
        ], 200);
    }

    /**
     * Delete uploaded PAN or GST/FSSAI document for a user.
     *
     * Payload:
     * - user_id (required)
     * - file_type (required): pan | gst_fssai
     *
     * Note: route is protected with portal.session_or_token middleware,
     * so user can only delete own files.
     */
    public function deleteUserUploadedDocument(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'file_type' => 'required|in:pan,gst_fssai',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = (int) $request->user_id;
        $fileType = (string) $request->file_type;

        $attachment = WebUserAttachment::where('user_id', $userId)->first();
        if (! $attachment) {
            return response()->json([
                'status' => false,
                'message' => 'No uploaded document found for this user.',
            ], 404);
        }

        if ($fileType === 'pan') {
            $fileName = trim((string) $attachment->panCard);
            if ($fileName === '') {
                return response()->json([
                    'status' => false,
                    'message' => 'PAN document is already empty.',
                ], 404);
            }

            $pathsToDelete = [
                public_path('webPortal/' . $userId . '/attachments/pan/' . $fileName),
            ];
            foreach ($pathsToDelete as $path) {
                if ($path && File::exists($path)) {
                    @File::delete($path);
                }
            }

            $attachment->update(['panCard' => null]);

            return response()->json([
                'status' => true,
                'message' => 'PAN document deleted successfully.',
            ], 200);
        }

        // gst_fssai delete (supports new combined path + legacy gstCard/fssaiCard)
        $gstFssaiPath = trim((string) $attachment->getRawOriginal('gst_fssai'));
        $gstLegacy = trim((string) $attachment->gstCard);
        $fssaiLegacy = trim((string) $attachment->fssaiCard);

        if ($gstFssaiPath === '' && $gstLegacy === '' && $fssaiLegacy === '') {
            return response()->json([
                'status' => false,
                'message' => 'GST/FSSAI document is already empty.',
            ], 404);
        }

        $relativeCandidates = [];
        if ($gstFssaiPath !== '') {
            $relativeCandidates[] = ltrim($gstFssaiPath, '/');
        }
        if ($gstLegacy !== '') {
            $relativeCandidates[] = 'gst/' . ltrim($gstLegacy, '/');
        }
        if ($fssaiLegacy !== '') {
            $relativeCandidates[] = 'fssai/' . ltrim($fssaiLegacy, '/');
        }

        foreach (array_unique($relativeCandidates) as $rel) {
            $full = public_path('webPortal/' . $userId . '/attachments/' . $rel);
            if (File::exists($full)) {
                @File::delete($full);
            }
        }

        $attachment->update([
            'gst_fssai' => null,
            'gstCard' => null,
            'fssaiCard' => null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'GST/FSSAI document deleted successfully.',
        ], 200);
    }

    /**
     * POST /api/portal/web/plans/by-role-category
     * Payload: { "role": 4, "category": 2 }
     */
    public function getWebPlansByRoleCategory(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'role' => 'required|integer|exists:roles,id',
            'category' => 'required|integer|exists:category,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $roleId = (int) $request->role;
        $categoryId = (int) $request->category;

        $webKeys = WebPlanKeysModel::select(['id', 'key', 'status', 'order_no'])
            ->where('status', 1)
            ->orderByRaw('order_no IS NULL, order_no ASC')
            ->orderBy('id')
            ->get()
            ->pluck('key', 'id');
        $activeWebKeyIds = $webKeys->keys()->map(fn($id) => (int) $id)->toArray();

        $plans = WebPlanModel::select([
                'id',
                'title',
                'short_description',
                'description',
                'status',
                'monthly_price',
                'quarterly_price',
                'yearly_price',
                'monthly_discount_percentage',
                'quarterly_discount_percentage',
                'yearly_discount_percentage',
                'monthly_final_amount',
                'quarterly_final_amount',
                'yearly_final_amount',
                'monthly_gst',
                'quarterly_gst',
                'yearly_gst',
            ])
            ->where('status', 1)
            ->where('role_id', $roleId)
            ->where('category_id', $categoryId)
            ->with(['getPlanKeyMap:key_id,plan_id'])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($q) use ($activeWebKeyIds) {
                // Avoid IEEE-754 noise in JSON (long float tails) by rounding to 2 decimals via string.
                $roundMoney = function ($value) {
                    if ($value === null || $value === '') {
                        return null;
                    }

                    return json_decode(sprintf('%.2f', (float) $value));
                };

                $afterDiscount = function ($price, $discountPct) use ($roundMoney) {
                    if ($price === null || $price === '') {
                        return null;
                    }
                    $p = (float) $price;
                    $d = (float) ($discountPct ?? 0);

                    return $roundMoney($p * (1 - $d / 100));
                };

                return [
                    'plan' => [
                        'id' => $q->id,
                        'title' => $q->title,
                        'short_description' => $q->short_description,
                        'description' => $q->description,
                        'status' => $q->status,
                    ],
                    'pricing' => [
                        'monthly' => [
                            'price' => $q->monthly_price,
                            'discount_percentage' => $q->monthly_discount_percentage,
                            'afterDiscountValue' => round($afterDiscount($q->monthly_price, $q->monthly_discount_percentage)),
                            'final_amount' => round($roundMoney($q->monthly_final_amount)),
                            'gst' => $q->monthly_gst,
                        ],
                        'quarterly' => [
                            'price' => $q->quarterly_price,
                            'discount_percentage' => $q->quarterly_discount_percentage,
                            'afterDiscountValue' => round($afterDiscount($q->quarterly_price, $q->quarterly_discount_percentage)),
                            'final_amount' => round($roundMoney($q->quarterly_final_amount)),
                            'gst' => $q->quarterly_gst,
                        ],
                        'yearly' => [
                            'price' => $q->yearly_price,
                            'discount_percentage' => $q->yearly_discount_percentage,
                            'afterDiscountValue' => round($afterDiscount($q->yearly_price, $q->yearly_discount_percentage)),
                            'final_amount' => round($roundMoney($q->yearly_final_amount)),
                            'gst' => $q->yearly_gst,
                        ],
                    ],
                    'availableKeys' => $q->getPlanKeyMap
                        ->pluck('key_id')
                        ->map(fn($id) => (int) $id)
                        ->filter(fn($id) => in_array($id, $activeWebKeyIds))
                        ->values(),
                ];
            })
            ->toArray();

        return response()->json([
            'status' => true,
            'message' => 'Web plan list fetched successfully',
            'data' => [
                'role' => $roleId,
                'category' => $categoryId,
                'plans' => $plans,
                'webKeys' => $webKeys,
            ]
        ], 200);
    }

    /**
     * GET /api/portal/years/closure-status
     * Returns last 5 years with is_closed true/false depending on whether
     * all active rice forms have a closing recorded for that cropYear.
     */
    public function getYearClosureStatus()
    {
        // Take last 3 distinct crop years present in live_prices (desc)
        $years = \App\LivePrice::query()
            ->whereNotNull('cropYear')
            ->select('cropYear')
            ->distinct()
            ->orderBy('cropYear', 'desc')
            ->limit(3)
            ->pluck('cropYear')
            ->map(fn($y) => (int) $y)
            ->toArray();

        $data = array_map(function ($year) {
            // All (name, form) pairs that have any live price for this year
            $requiredPairs = \App\LivePrice::query()
                ->where('cropYear', $year)
                ->where('status', 1)
                ->whereNotNull('name')
                ->whereNotNull('form')
                ->where('closing', '!=', null)
                ->where('closing', '>', 0)
                ->first();

                $isClosed = ($year == 2023) ? true : ($requiredPairs !== null);

            return [
                'year' => (int) $year,
                'is_closed' => (bool) $isClosed,
            ];
        }, $years);

        return response()->json([
            'status' => true,
            'message' => 'Years closure status',
            'data' => $data
        ], 200);
    }

    // POST /api/portal/create-order
    public function webCreateOrder(Request $request) {

        $userId = $request->user_id;
        $planId = $request->plan_id;
        $amount = $request->amount;
        $currency = $request->currency ?? 'INR';
        $billingPeriod = $request->billing_period;
        $subscriptionType = (string) $request->subscription_type;

        // Trial activation: no Razorpay order is created (Razorpay rejects 0-amount orders).
        // Create the subscription row, mirror the trial side-effects of webVerifyPayment, and return.
        if ($subscriptionType === 'trial') {
            $subscription = $this->activateTrialSubscription((int) $userId, (int) $planId);

            return response()->json([
                'status' => true,
                'trial_activated' => true,
                'order_id' => null,
                'amount' => 0,
                'currency' => $currency,
                'message' => '✅ Free trial activated.',
                'data' => $subscription,
            ]);
        }

        $api = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );

        $order = $api->order->create([
            'amount' => $amount * 100, // amount in paise
            'currency' => $currency,
            'receipt' => 'receipt_' . $userId . '_' . time(),
            'notes' => [
                'user_id' => $userId,
                'plan_id' => $planId,
                'billing_period' => $billingPeriod,
            ],
        ]);

        return response()->json([
            'status' => true,
            'trial_activated' => false,
            'order_id' => $order['id'],
            'amount' => $amount,
            'currency' => $currency,
        ]);
    }

    /**
     * Create a 30-day trial subscription row, refresh the user's profile validation status,
     * and dispatch the trial activation + admin notification emails. Used by both
     * `webCreateOrder` (no payment) and `webVerifyPayment` (legacy paid path that still receives trial).
     */
    private function activateTrialSubscription(int $userId, int $planId): WebUserSubscriptionModel
    {
        $addedDays = $this->getSubscriptionAddedDays('trial');
        $subscriptionStart = $this->getNextSubscriptionStartDate($userId);
        $subscriptionEnd = (clone $subscriptionStart)->addDays($addedDays);

        $trialReference = 'trial_' . $userId . '_' . time();
        $subscription = WebUserSubscriptionModel::create([
            'user_id'           => $userId,
            'plan_id'           => $planId,
            'payment_id'        => $trialReference,
            'order_id'          => $trialReference,
            'period_start'      => $subscriptionStart->format('Y-m-d'),
            'period_end'        => $subscriptionEnd->format('Y-m-d'),
            'subscription_type' => 'trial',
            'status'            => 1,
        ]);

        $userDetails = User::where('id', $userId)->first();

        $webUserAttachment = WebUserAttachment::where('user_id', $userId)->first();
        if ($webUserAttachment === null || ! $webUserAttachment->trialDocumentsComplete()) {
            User::where('id', $userId)->update(['has_validation' => 'Please submit your documents to complete your profile.']);
        } else {
            User::where('id', $userId)->update(['has_validation' => 'Your profile is under review. We will notify you once approved.']);
        }

        if ($userDetails) {
            $userName  = (string) ($userDetails->name ?? '');
            $userEmail = (string) ($userDetails->email ?? '');

            if ($userEmail !== '') {
                Mail::to($userEmail)->queue(new WebTrialActivatedUserMail($userName, $userEmail));
            }

            Mail::to('info@sntcgroup.com')->queue(new NewUserRegistrationAdminMail($userName, $userEmail));
        }

        return $subscription;
    }


    public function webVerifyPayment(Request $request)
    {
        $razorpayPaymentId = $request->razorpay_payment_id;
        $razorpayOrderId   = $request->razorpay_order_id;
        $razorpaySignature = $request->razorpay_signature;
        $userId            = $request->user_id;
        $planId            = $request->plan_id;
        $subscriptionType  = (string) $request->subscription_type;

        // Defensive: trial flow should not hit verify-payment (no Razorpay order is created),
        // but if a legacy client still calls it, activate the trial without signature checks.
        if ($subscriptionType === 'trial') {
            $subscription = $this->activateTrialSubscription((int) $userId, (int) $planId);
            $totalAvailableDays = $this->getTotalAvailableSubscriptionDays((int) $userId);

            return response()->json([
                'status'  => true,
                'message' => '✅ Free trial activated.',
                'total_available_days' => $totalAvailableDays,
                'data'    => $subscription,
            ]);
        }

        // Initialize Razorpay API with correct credentials
        $api = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );

        // Prepare attributes for verification
        $attributes = [
            'razorpay_order_id'   => $razorpayOrderId,
            'razorpay_payment_id' => $razorpayPaymentId,
            'razorpay_signature'  => $razorpaySignature
        ];

        try {
            // Verify payment signature
            $api->utility->verifyPaymentSignature($attributes);
            $addedDays = $this->getSubscriptionAddedDays($subscriptionType);

            // Always chain renewal from the latest available plan end date (current or queued future plans).
            $subscriptionStart = $this->getNextSubscriptionStartDate((int) $userId);
            $subscriptionEnd = (clone $subscriptionStart)->addDays($addedDays);

            // ✅ Signature matched successfully — store the subscription/payment
            $subscription = WebUserSubscriptionModel::create([
                'user_id'      => $userId,
                'plan_id'      => $planId,
                'payment_id'   => $razorpayPaymentId,
                'order_id'     => $razorpayOrderId,
                'status'       => 'active',
                'period_start' => $subscriptionStart->format('Y-m-d'),
                'period_end'   => $subscriptionEnd->format('Y-m-d'),
                'subscription_type' => $subscriptionType,
                'status' => 1
            ]);

            $totalAvailableDays = $this->getTotalAvailableSubscriptionDays((int) $userId);

            $userDetails = User::where(['id' => $userId])->first();
            if ($userDetails === null) {
                return response()->json([
                    'status'  => true,
                    'message' => '✅ Payment verified and subscription activated.',
                    'total_available_days' => $totalAvailableDays,
                    'data'    => $subscription
                ]);
            }

            $mailTo = $userDetails->email ?? '';
            $mailMessage = '';
            $subject = 'Subscription Activated – Welcome to SNTC';
            $mailFrom = 'info@sntcgroup.com';
            $mailFromName = 'SNTC Team - India';

            $invoiceAttach = null;
            try {
                $paidTotal = 0.0;
                $currency = 'INR';
                try {
                    $rzpPayment = $api->payment->fetch($razorpayPaymentId);
                    $paidTotal = ((float) ($rzpPayment['amount'] ?? 0)) / 100;
                    $currency = (string) ($rzpPayment['currency'] ?? 'INR');
                } catch (\Throwable $e) {
                    $planForAmount = WebPlanModel::find($planId);
                    $paidTotal = $this->planFinalAmount($planForAmount, $subscriptionType);
                }

                $plan = WebPlanModel::find($planId);
                $invoiceAttach = (new PaymentInvoiceService())->makePdf(
                    $userDetails,
                    $subscription,
                    $plan,
                    $paidTotal,
                    $currency
                );

                $paymentUpdate = [];
                if (Schema::hasColumn('web_user_subscription', 'amount')) {
                    $paymentUpdate['amount'] = $paidTotal;
                }
                if (Schema::hasColumn('web_user_subscription', 'currency')) {
                    $paymentUpdate['currency'] = $currency;
                }
                if (is_array($invoiceAttach) && ! empty($invoiceAttach['filename']) && Schema::hasColumn('web_user_subscription', 'invoice_path')) {
                    $paymentUpdate['invoice_path'] = 'invoices/'.$invoiceAttach['filename'];
                }
                if ($paymentUpdate) {
                    $subscription->fill($paymentUpdate);
                    $subscription->save();
                }
            } catch (\Throwable $e) {
                report($e);
            }

            $data = ['userName' => $userDetails->name , 'userEmail' => $userDetails->email];
            if ($mailTo !== '') {
                try {
                    Mail::send('mail.AccrountActiveWebMail', $data, function ($message) use ($mailTo, $mailMessage, $subject, $mailFrom, $mailFromName, $invoiceAttach) {
                        $message->to($mailTo, $mailMessage)->subject($subject);
                        $message->from($mailFrom, $mailFromName);
                        if (is_array($invoiceAttach) && ! empty($invoiceAttach['path']) && is_file($invoiceAttach['path'])) {
                            $message->attach($invoiceAttach['path'], [
                                'as' => ($invoiceAttach['filename'] ?? 'SNTC-Invoice.pdf'),
                                'mime' => 'application/pdf',
                            ]);
                        }
                    });
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            return response()->json([
                'status'  => true,
                'message' => '✅ Payment verified and subscription activated.',
                'total_available_days' => $totalAvailableDays,
                'data'    => $subscription
            ]);

        } catch (Exception $e) {
            // ❌ Signature verification failed
            return response()->json([
                'status'  => false,
                'message' => '❌ Payment verification failed.',
                'error'   => $e->getMessage(),
            ], 400);
        }
    }

    public function getLatestUpdatedCount()
    {
        $todayDate = Carbon::now()->format('Y-m-d');
        $livePricesCount = LivePrice::whereDate('created_at' ,  $todayDate)->where('status' , 1)->count();
        $paddyMandiCount = PaddyPrice::whereDate('created_at' , $todayDate)->where('status' , 1)->count();
        $tradeCount = TradeQueriesINR::whereDate('created_at' , $todayDate)->where('status' , 1)->count();

        return response()->json([
                'status'  => true,
                'message' => 'count get successfully',
                'data' => ['liveCount' => $livePricesCount , 'paddyCount' => $paddyMandiCount , 'tradeCount' => $tradeCount ] 
            ], 200);
    }

    /**
     * Get current user session from cookie
     * GET /api/portal/session
     */
    public function getSession(Request $request)
    {
        // 1) Preferred: session-cookie auth
        if (auth('web')->check()) {
            return $this->buildPortalSessionResponse((int) auth('web')->id());
        }

        // 2) Fallback: API token auth (helps when cross-site cookie is blocked)
        $token = null;
        $authHeader = $request->header('Authorization');
        if ($authHeader && preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
            $token = trim($matches[1]);
        }
        if (! $token) {
            $token = $request->header('X-API-TOKEN');
        }

        if (! $token) {
            return response()->json(['status' => false, 'message' => 'Not authenticated'], 401);
        }

        $platform = $this->resolveLoginPlatform($request);
        $allowedFrom = config('portal.api_token_user_from', ['web']);
        $allowNullFrom = (bool) config('portal.api_token_allow_null_user_from', true);

        $user = User::findByPortalApiToken($token, function ($query) use ($allowedFrom, $allowNullFrom) {
            $query->where('userType', 2)
                ->where(function ($q) use ($allowedFrom, $allowNullFrom) {
                    $q->whereIn('user_from', $allowedFrom);
                    if ($allowNullFrom) {
                        $q->orWhereNull('user_from')->orWhere('user_from', '');
                    }
                });
        }, $platform);

        if (! $user) {
            return response()->json(['status' => false, 'message' => 'Not authenticated'], 401);
        }

        // Re-create web session so future calls can work with cookies.
        auth('web')->login($user);
        $request->session()->save();

        return $this->buildPortalSessionResponse((int) $user->id);
    }

    /**
     * True when personal and/or business profile rows exist.
     * Document-only uploads (PAN / GST-FSSAI / farmer file) do not count as "basic details".
     */
    private function portalUserHasBasicProfileDetails(User $user): bool
    {
        return $user->getWebPersonalDetails != null || $user->getWebBusinessDetails != null;
    }

    /**
     * True when at least one onboarding document exists (PAN, GST/FSSAI, or farmer file).
     */
    private function portalUserHasUploadedDocuments(User $user): bool
    {
        $attachment = $user->getWebUserAttachment;
        if ($attachment === null) {
            return false;
        }

        if (! empty(trim((string) $attachment->panCard))) {
            return true;
        }

        if (! empty(trim((string) $attachment->farmer_file))) {
            return true;
        }

        $gstFssaiPath = $attachment->resolveGstFssaiRelativePath();

        return $gstFssaiPath !== null && $gstFssaiPath !== '';
    }

    private function getTotalAvailableSubscriptionDays(int $userId): int
    {
        $subscriptions = WebUserSubscriptionModel::where('user_id', $userId)
            ->whereDate('period_end', '>=', Carbon::now()->format('Y-m-d'))
            ->where(function ($q) {
                $q->where('status', 1)->orWhereNull('status');
            })
            ->get(['period_start', 'period_end']);

        $today = Carbon::now()->startOfDay();
        $totalDays = 0;
        foreach ($subscriptions as $row) {
            if (! $row->period_end) {
                continue;
            }

            $effectiveStart = $row->period_start
                ? Carbon::parse($row->period_start)->startOfDay()
                : $today->copy();
            if ($effectiveStart->lt($today)) {
                $effectiveStart = $today->copy();
            }

            $effectiveEnd = Carbon::parse($row->period_end)->startOfDay();
            if ($effectiveEnd->gte($effectiveStart)) {
                $totalDays += $effectiveStart->diffInDays($effectiveEnd) + 1;
            }
        }

        return $totalDays;
    }

    private function getNextSubscriptionStartDate(int $userId): Carbon
    {
        $lastAvailablePlan = WebUserSubscriptionModel::where('user_id', $userId)
            ->whereDate('period_end', '>=', Carbon::now()->format('Y-m-d'))
            ->where(function ($q) {
                $q->where('status', 1)->orWhereNull('status');
            })
            ->orderBy('period_end', 'desc')
            ->first(['period_end']);

        if (! $lastAvailablePlan || ! $lastAvailablePlan->period_end) {
            return Carbon::now()->startOfDay();
        }

        return Carbon::parse($lastAvailablePlan->period_end)->addDay()->startOfDay();
    }

    private function buildPortalSessionResponse(int $userId)
    {
        $data = User::where('id', $userId)
            ->where('userType', 2)
            ->with(array_merge(
                $this->portalPersonalDetailsWithRelations(),
                [
                    'getWebBusinessDetails' => function ($q) {
                        return $q->with(['getCategoryDetails:id,category']);
                    },
                    'getWebUserAttachment',
                    'getWebUserSubscription' => function ($q) {
                        return $q->whereDate('period_end', '>=', Carbon::now()->format('Y-m-d'));
                    },
                    'role_rel',
                ]
            ))
            ->first();

        if (! $data) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        if ($blocked = $this->portalAccessBlockedResponse($data)) {
            return $blocked;
        }

        $this->hydratePersonalLocationRelations($data->getWebPersonalDetails);

        $hasActivePlan = (bool) $data->getWebUserSubscription;
        $hasBasicDetails = $this->portalUserHasBasicProfileDetails($data);
        $hasUploadedDocuments = $this->portalUserHasUploadedDocuments($data);
        $hasTrialDone = WebUserSubscriptionModel::where('user_id', $userId)
            ->where('subscription_type', 'trial')
            ->exists();

        $userArray = $data->toArray();
        if (isset($userArray['get_web_business_details']['get_bag_vendor_web'])) {
            unset($userArray['get_web_business_details']['get_bag_vendor_web']);
        }

        return response()->json([
            'status' => true,
            'message' => 'Session restored',
            'hasBasicDetails' => $hasBasicDetails,
            'hasUploadedDocuments' => $hasUploadedDocuments,
            'hasTrialDone' => $hasTrialDone,
            'hasActivePlan' => $hasActivePlan,
            'total_available_days' => $this->getTotalAvailableSubscriptionDays($userId),
            'planDetails' => $data->getWebUserSubscription,
            'data' => $userArray,
            'prefix' => [
                'avatar' => 'webPortal/' . $userId . '/attachments/avatar',
                'pan' => 'webPortal/' . $userId . '/attachments/pan',
                'attachments' => 'webPortal/' . $userId . '/attachments',
                'gst_fssai' => 'webPortal/' . $userId . '/attachments/gst_fssai',
            ],
        ], 200);
    }

    /**
     * Log out web user: clear web guard, invalidate session, rotate CSRF token.
     *
     * Routes: POST /api/portal/logout, POST /api/web/logout
     */
    public function logout(Request $request)
    {
        $platform = $request->attributes->get('auth_platform')
            ?: $this->resolveLoginPlatform($request);

        $token = null;
        $authHeader = $request->header('Authorization');
        if ($authHeader && preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
            $token = trim($matches[1]);
        }
        if (! $token) {
            $token = $request->header('X-API-TOKEN');
        }

        if ($token) {
            $user = User::findByPortalApiToken($token, null, $platform);
            if ($user) {
                $platform = $user->getAttribute('auth_platform') ?: $platform;
                $column = ClientPlatform::tokenColumn($platform);
                User::where('id', $user->id)->update([$column => null]);
            }
        } elseif (auth('web')->check()) {
            // Cookie session logout without Bearer: clear web token only.
            User::where('id', auth('web')->id())->update(['api_token' => null]);
        }

        auth('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $response = response()->json([
            'status' => true,
            'message' => 'Logged out successfully',
            'platform' => $platform,
        ], 200);

        return $this->withExpiredWebAuthCookies($response);
    }

    /**
     * Expire session + CSRF cookies so the browser removes them (path/domain/secure/samesite must match how they were set).
     */
    private function withExpiredWebAuthCookies(Response $response): Response
    {
        $path = config('session.path') ?: '/';
        $domain = config('session.domain');
        $secure = config('session.secure');
        if (! is_bool($secure)) {
            $secure = (bool) $secure;
        }
        $httpOnly = (bool) config('session.http_only', true);
        $sameSite = config('session.same_site');
        if ($sameSite === null || $sameSite === '') {
            $sameSite = SymfonyCookie::SAMESITE_LAX;
        } elseif (is_string($sameSite)) {
            $sameSite = strtolower($sameSite);
        }
        $expire = time() - 3600;

        $response->headers->setCookie(SymfonyCookie::create(
            config('session.cookie'),
            '',
            $expire,
            $path,
            $domain,
            $secure,
            $httpOnly,
            false,
            $sameSite
        ));

        // CSRF cookie (readable by JS); clear so SPA does not keep stale token
        $response->headers->setCookie(SymfonyCookie::create(
            'XSRF-TOKEN',
            '',
            $expire,
            $path,
            $domain,
            $secure,
            false,
            false,
            $sameSite
        ));

        return $response;
    }

    /**
     * Get web access permissions for a user
     * POST /api/portal/web-access
     * Payload: { "user_id": 123 }
     */
    public function getWebAccess(Request $request)
    {
        // Validate input
        $validator = \Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'plan_id' => 'nullable|integer|exists:web_plan,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->user_id;

        // Get user with relationships
        $user = User::where('id', $userId)
            ->where('userType', 2)
            ->with([
                'getWebBusinessDetails' => function($q) {
                    return $q->with(['getCategoryDetails:id,category']);
                },
                'getWebUserSubscription' => function($q) {
                    return $q->whereDate('period_end', '>=', Carbon::now()->format('Y-m-d'))
                        ->orderBy('id', 'desc');
                },
                'role_rel:id,role_name'
            ])
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Get user's role
        $roleId = $user->role;
        if (!$roleId) {
            return response()->json([
                'status' => false,
                'message' => 'User role not found'
            ], 404);
        }

        // Get user's category from business details
        $categoryId = null;
        if ($user->getWebBusinessDetails && $user->getWebBusinessDetails->selected_category) {
            $categoryId = (int) $user->getWebBusinessDetails->selected_category;
        }

        // Check for active subscription (period_end >= today)
        $subscription = WebUserSubscriptionModel::where('user_id', $userId)
            ->whereDate('period_end', '>=', Carbon::now()->format('Y-m-d'))
            ->where(function ($q) {
                $q->where('status', 1)->orWhereNull('status');
            })
            ->orderBy('id', 'desc')
            ->first();
        
        if (!$subscription) {
            return response()->json([
                'status' => false,
                'message' => 'No active plan available'
            ], 200);
        }

        // Get plan_id from active subscription (optional override from client)
        $planId = $request->filled('plan_id')
            ? (int) $request->plan_id
            : (int) $subscription->plan_id;

        // All web_plan rows for this role + category (subscription plan may not match web_access.plan_id)
        $plansForRoleCategory = WebPlanModel::query()
            ->where('role_id', $roleId)
            ->where('status', 1)
            ->when($categoryId !== null, function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            }, function ($q) {
                $q->whereNull('category_id');
            })
            ->pluck('id');

        $candidatePlanIds = collect([$planId])
            ->merge($plansForRoleCategory)
            ->unique()
            ->filter()
            ->values()
            ->all();

        // Build query for web_access:
        // - match subscription / same role+category plan ids
        // - OR plan_id IS NULL (saved as "any plan" for that role/category in admin)
        $webAccessQuery = WebAccess::where('role_id', $roleId)
            ->where('status', 1)
            ->where(function ($q) use ($candidatePlanIds) {
                $q->whereIn('plan_id', $candidatePlanIds)
                    ->orWhereNull('plan_id');
            })
            ->with(['webSideMenu:id,title,sub_title,slug,create_url,read_url,update_url,delete_url,sort_order']);

        // Add category filter if category exists
        if ($categoryId) {
            $webAccessQuery->where(function($q) use ($categoryId) {
                $q->where('category_id', $categoryId)
                  ->orWhereNull('category_id');
            });
        } else {
            $webAccessQuery->whereNull('category_id');
        }

        // Get web access permissions and order by menu sort_order
        $webAccess = $webAccessQuery->get()->unique('web_side_menu_id')->sortBy(function($access) {
            // Sort by menu's sort_order, then by menu id if sort_order is same
            if ($access->webSideMenu) {
                return [$access->webSideMenu->sort_order ?? 999999, $access->webSideMenu->id ?? 999999];
            }
            return [999999, 999999]; // Put items without menu at the end
        })->values(); // Reset keys after sorting

        // Format response (already sorted by sort_order)
        $permissions = $webAccess->map(function($access) {
            return [
                'menu_id' => $access->web_side_menu_id,
                'menu_title' => $access->webSideMenu ? $access->webSideMenu->title : null,
                'menu_sub_title' => $access->webSideMenu ? $access->webSideMenu->sub_title : null,
                'menu_slug' => $access->webSideMenu ? $access->webSideMenu->slug : null,
                'menu_sort_order' => $access->webSideMenu ? $access->webSideMenu->sort_order : null,
                'urls' => [
                    'create_url' => $access->webSideMenu ? $access->webSideMenu->create_url : null,
                    'read_url' => $access->webSideMenu ? $access->webSideMenu->read_url : null,
                    'update_url' => $access->webSideMenu ? $access->webSideMenu->update_url : null,
                    'delete_url' => $access->webSideMenu ? $access->webSideMenu->delete_url : null,
                ],
                'permissions' => [
                    'can_create' => (bool) $access->can_create,
                    'can_read' => (bool) $access->can_read,
                    'can_update' => (bool) $access->can_update,
                    'can_delete' => (bool) $access->can_delete,
                ]
            ];
        });

        // Last 4 crop years from live_prices and access per plan (allowed_years)
        $lastYears = \App\LivePrice::query()
            ->whereNotNull('cropYear')
            ->select('cropYear')
            ->distinct()
            ->orderBy('cropYear', 'desc')
            ->limit(3)
            ->pluck('cropYear')
            ->map(fn($y) => (int) $y)
            ->toArray();
        $allowedYearsUnion = collect($webAccess)->pluck('allowed_years')->filter()->flatten()->map(fn($y) => (int) $y)->unique()->toArray();
        $yearsAccess = array_map(function($y) use ($allowedYearsUnion){
            $closedRow = \App\LivePrice::query()
                ->where('cropYear', $y)
                ->where('status', 1)
                ->whereNotNull('name')
                ->whereNotNull('form')
                ->where('closing', '!=', null)
                ->where('closing', '>', 0)
                ->first();
            return [
                'year' => (int)$y,
                'has_access' => in_array((int)$y, $allowedYearsUnion),
                'is_closed' => $closedRow !== null
            ];
        }, $lastYears);

        // Fetch plan name
        $planTitle = null;
        if ($planId) {
            $planTitle = \App\WebPlanModel::where('id', $planId)->value('title');
        }

        // Paddy trades shown on portal/app (Active, In-Process, Hold, Sold — not Deactivated)
        $paddyTradeCount = PaddyTrade::query()
            ->whereIn('status', PaddyTrade::$listableStatuses)
            ->count();
        $paddyMarketStatus = PaddyTradeCurrentStatus::current();

        return response()->json([
            'status' => true,
            'message' => 'Web access permissions retrieved successfully',
            'data' => [
                'user_id' => $userId,
                'plan_id' => $planId,
                'plan_name' => $planTitle,
                'role' => [
                    'id' => $roleId,
                    'name' => $user->role_rel ? $user->role_rel->role_name : null
                ],
                'category' => [
                    'id' => $categoryId,
                    'name' => $user->getWebBusinessDetails && $user->getWebBusinessDetails->getCategoryDetails 
                        ? $user->getWebBusinessDetails->getCategoryDetails->category 
                        : null
                ],
                'plan' => [
                    'id' => $planId,
                    'subscription_type' => $subscription->subscription_type,
                    'period_start' => $subscription->period_start,
                    'period_end' => $subscription->period_end
                ],
                'web_access' => $permissions,
                'years' => $yearsAccess,
                'paddy_trade_count' => $paddyTradeCount,
                'paddy_market_status' => [
                    'currentStatus' => (int) $paddyMarketStatus->currentStatus,
                    'label' => $paddyMarketStatus->status_label,
                    'message' => $paddyMarketStatus->message,
                ],
            ]
        ], 200);
    }

    /**
     * Subscription history for a web user (latest first).
     * Payload: { "user_id": 123 }
     */
    public function getWebSubscriptionHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = (int) $request->input('user_id');

        $history = WebUserSubscriptionModel::where('user_id', $userId)
            ->with(['planRel'])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($row) {
                return [
                    'date_of_payment' => $row->created_at ? Carbon::parse($row->created_at)->format('Y-m-d H:i:s') : null,
                    'plan' => $row->planRel ? $row->planRel->title : null,
                    'plan_type' => $row->subscription_type,
                    'purchased_on' => $row->created_at ? Carbon::parse($row->created_at)->format('Y-m-d') : null,
                    'start_date' => $row->period_start,
                    'end_date' => $row->period_end,
                ];
            })
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Subscription history fetched successfully.',
            'data' => $history,
        ], 200);
    }

    /**
     * In-app notifications for web portal (from admin "Notify Web User").
     * Authenticated by Bearer / X-API-TOKEN (portal.api.token); list is for the token owner only.
     * Optional: limit (default 100, max 200). Legacy POST body user_id is still accepted if it matches the token.
     */
    public function getWebPortalNotifications(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'limit' => 'nullable|integer|min:1|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $limit = (int) $request->input('limit', 100);
        $userId = (int) $user->id;

        $rows = WebUserNotification::where('user_id', $userId)
            ->where('is_cleared', 0)
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'title' => $row->title,
                    'message' => $row->message,
                    'notify_date' => $row->notify_date ? $row->notify_date->format('Y-m-d') : null,
                    'read_at' => $row->read_at ? Carbon::parse($row->read_at)->format('Y-m-d H:i:s') : null,
                    'created_at' => $row->created_at ? Carbon::parse($row->created_at)->format('Y-m-d H:i:s') : null,
                ];
            })
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Notifications fetched successfully.',
            'data' => $rows,
        ], 200);
    }

    /**
     * Soft-clear all web portal notifications for the authenticated token owner.
     */
    public function clearWebPortalNotifications(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $cleared = WebUserNotification::where('user_id', (int) $user->id)
            ->where('is_cleared', 0)
            ->update(['is_cleared' => 1]);

        return response()->json([
            'status' => true,
            'cleared' => (int) $cleared,
        ], 200);
    }

    /**
     * API 1: Get mapped rice qualities (rice names like 1121, 1509, etc.)
     * Only returns rice names configured in web_rice_form_map for portal interests.
     */
    public function getRiceQualitiesList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|in:basmati,non-basmati',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $riceType = $request->input('type');

        $mappedRiceNameIds = WebRiceFormMap::query()
            ->join('rice_names as rn', 'rn.id', '=', 'web_rice_form_map.rice_name_id')
            ->when($riceType, function ($q) use ($riceType) {
                $q->where('rn.type', $riceType)
                    ->where(function ($q2) use ($riceType) {
                        // Include old mapping rows where rice_type was not stored yet.
                        $q2->where('web_rice_form_map.rice_type', $riceType)
                            ->orWhereNull('web_rice_form_map.rice_type');
                    });
            })
            ->distinct()
            ->pluck('web_rice_form_map.rice_name_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values();

        $riceNames = RiceName::query()
            ->whereIn('id', $mappedRiceNameIds->all())
            ->orderBy('order', 'asc')
            // ->orderBy('id', 'asc')
            ->get(['id', 'name', 'type','order']);

        $selected = (object) [];
        $canEditByAdmin = null;
        if ($request->filled('user_id')) {
            $userId = (int) $request->user_id;
            $rows = UserInterestedMap::where('user_id', $userId)
                ->where('status', 1)
                ->whereIn('rice_name_id', $mappedRiceNameIds->all())
                ->get(['rice_name_id', 'form_id', 'grade']);

            $selected = $rows
                ->groupBy('rice_name_id')
                ->map(function ($items) {
                    return $items
                        ->groupBy('form_id')
                        ->map(function ($formItems) {
                            return $formItems
                                ->pluck('grade')
                                ->filter(fn ($g) => $g !== null && $g !== '')
                                ->map(fn ($g) => (int) $g)
                                ->unique()
                                ->values();
                        });
                });

            if ($selected->isEmpty()) {
                $selected = (object) [];
            }

            $user = User::find($userId);
            if ($user) {
                $canEditByAdmin = (int) ($user->can_edit_by_admin ?? 0);
            }
        }

        return response()->json([
            'status'   => true,
            'message'  => 'Rice qualities fetched successfully.',
            'data'     => $riceNames,
            'selected' => $selected,
            'can_edit_by_admin' => $canEditByAdmin,
        ]);
    }

    /**
     * Rice forms for a rice name from web_rice_form_map (portal interest picker).
     * GET portal/interested/rice-forms?riceId={id}&user_id={optional}
     */
    public function getRiceFormsList(Request $request)
    {
        $riceId = $request->input('riceId', $request->input('rice_id'));

        $validator = Validator::make(
            array_merge($request->all(), ['riceId' => $riceId]),
            [
                'riceId' => 'required|integer|exists:rice_names,id',
                'user_id' => 'nullable|integer|exists:users,id',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $riceId = (int) $riceId;
        $riceName = RiceName::find($riceId);

        $maps = WebRiceFormMap::query()
            ->where('rice_name_id', $riceId)
            ->when($riceName?->type, function ($q) use ($riceName) {
                $q->where(function ($q2) use ($riceName) {
                    $q2->where('rice_type', $riceName->type)->orWhereNull('rice_type');
                });
            })
            ->get(['form_ids']);

        $formIdSet = [];
        foreach ($maps as $map) {
            foreach ($this->normalizeFormIdsFromMap($map->form_ids) as $formId) {
                $formIdSet[$formId] = true;
            }
        }
        $formIds = array_keys($formIdSet);

        if ($formIds === []) {
            return response()->json([
                'status' => true,
                'message' => 'Rice forms fetched successfully.',
                'data' => [],
                'selected' => (object) [],
                'rice_id' => $riceId,
            ]);
        }

        $forms = RiceFormMilestone3::query()
            ->where('status', 1)
            ->whereIn('id', $formIds)
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'name', 'order', 'status']);

        $selected = (object) [];
        if ($request->filled('user_id')) {
            $interestRows = UserInterestedMap::query()
                ->where('user_id', (int) $request->user_id)
                ->where('rice_name_id', $riceId)
                ->where('status', 1)
                ->get(['form_id', 'grade']);

            $selected = $interestRows
                ->groupBy('form_id')
                ->map(function ($items) {
                    return $items
                        ->pluck('grade')
                        ->filter(fn ($g) => $g !== null && $g !== '')
                        ->map(fn ($g) => (int) $g)
                        ->unique()
                        ->values();
                });

            if ($selected->isEmpty()) {
                $selected = (object) [];
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Rice forms fetched successfully.',
            'data' => $forms,
            'selected' => $selected,
            'rice_id' => $riceId,
        ]);
    }

    /**
     * API 3: Get wands based on rice_name_id and form_ids from web_rice_form_map table
     * Returns wand details with type + value (e.g. "Wand - 8.50+mm")
     *
     * Required params: rice_name_id, form_id
     */
    public function getWandsByRiceFormMap(Request $request)
    {
        $request->validate([
            'rice_name_id' => 'required|exists:rice_names,id',
            'form_id'      => 'required|exists:rice_form_milestone3,id',
        ]);

        $riceNameId = (int) $request->rice_name_id;
        $formId     = (int) $request->form_id;

        // `form_ids` is a JSON column; values may be stored either as a scalar
        // (e.g. 16 / "16") via the single-select form, or as an array. Match all
        // valid shapes for the given form_id.
        $formMap = WebRiceFormMap::where('rice_name_id', $riceNameId)
            ->where(function ($q) use ($formId) {
                $q->whereJsonContains('form_ids', $formId)
                    ->orWhereJsonContains('form_ids', (string) $formId)
                    ->orWhereRaw('CAST(form_ids AS UNSIGNED) = ?', [$formId]);
            })
            ->first();

        if (!$formMap || !$formMap->wand_ids) {
            return response()->json([
                'status'  => true,
                'message' => 'No wands found for the given rice name and form.',
                'data'    => [],
            ]);
        }

        // Get wand details with type + value format
        $wands = WandModel::with('getWandType')
            ->whereIn('id', $formMap->wand_ids)
            ->where('status', 1)
            ->orderBy('order')
            ->get()
            ->map(function ($wand) {
                return [
                    'id'    => $wand->id,
                    'label' => $wand->getWandType ? $wand->getWandType->type . ' - ' . $wand->value : $wand->value,
                    'type'  => $wand->getWandType ? $wand->getWandType->type : '',
                    'value' => $wand->value,
                ];
            })
            ->values();

        return response()->json([
            'status'  => true,
            'message' => 'Wands fetched successfully.',
            'data'    => $wands,
        ]);
    }

    public function saveUserInterestedMap(Request $request)
    {
        $interestedItems = $request->input('interested', $request->input('intrested'));
        if (! is_array($interestedItems)) {
            $interestedItems = [];
        }

        $validator = Validator::make(
            array_merge($request->all(), ['interested' => $interestedItems]),
            [
                'user_id' => 'required|exists:users,id',
                // 1 = let SNTC approve search experience; 0 = user manages interests themselves
                'can_edit_by_admin' => 'required|in:0,1',
                // Empty array is allowed so users can clear all preferred items.
                'interested' => 'present|array',
                'interested.*.name_id' => 'required|exists:rice_names,id',
                'interested.*.form_id' => 'required|exists:rice_form_milestone3,id',
                'interested.*.grades' => 'nullable|array',
                'interested.*.grades.*' => 'integer|exists:wand,id',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = (int) $request->user_id;
        $canEditByAdmin = (int) $request->input('can_edit_by_admin');

        try {
            User::where('id', $userId)->update(['can_edit_by_admin' => $canEditByAdmin]);

            $savedCount = \App\Services\UserInterestService::syncForUser($userId, $interestedItems);

            return response()->json([
                'status' => true,
                'message' => 'Interested data saved successfully.',
                'data' => [
                    'user_id' => $userId,
                    'saved_count' => $savedCount,
                    'can_edit_by_admin' => $canEditByAdmin,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @param  mixed  $formIds
     * @return int[]
     */
    private function normalizeFormIdsFromMap($formIds): array
    {
        if ($formIds === null || $formIds === '') {
            return [];
        }
        if (is_array($formIds)) {
            $out = [];
            foreach ($formIds as $v) {
                if (is_numeric($v)) {
                    $out[] = (int) $v;
                }
            }

            return array_values(array_unique($out));
        }
        if (is_numeric($formIds)) {
            return [(int) $formIds];
        }
        if (is_string($formIds)) {
            $decoded = json_decode($formIds, true);
            if (is_array($decoded)) {
                return $this->normalizeFormIdsFromMap($decoded);
            }
            if (is_numeric($formIds)) {
                return [(int) $formIds];
            }
        }

        return [];
    }
}
