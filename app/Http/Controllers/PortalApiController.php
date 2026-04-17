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
use App\WebUserAttachment;
use App\WebPlanModel;
use App\WebPlanKeysModel;
use App\WebUserSubscriptionModel;
use App\WebUserNotification;
use App\WebAccess;
use App\VendorUserMap;
use App\ServiceProviderUserMap;
use Razorpay\Api\Api;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

class PortalApiController extends Controller
{
    /** Maximum upload size for portal attachments (GST/PAN/farmer docs, avatar, etc.), in bytes. */
    private const PORTAL_UPLOAD_MAX_BYTES = 15 * 1024 * 1024;

    private function generateAndStoreApiToken(int $userId): string
    {
        do {
            $token = hash('sha256', Str::random(80) . microtime(true) . $userId);
        } while (User::where('api_token', $token)->exists());

        User::where('id', $userId)->update(['api_token' => $token]);

        return $token;
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
                $token = $this->generateAndStoreApiToken((int) $user->id);
                // ✅ Create Laravel session using 'web' guard (sets httpOnly cookie automatically)
                auth('web')->login($user);
                
                // ✅ Ensure session is saved so Set-Cookie header is sent
                $request->session()->save();
                
                // ✅ Reload user with relationships using the user ID to ensure we get the correct user
                $data = User::where('id', $user->id)->where('userType', 2)->with(['getWebPersonalDetails', 'getWebBusinessDetails' => function($q){
                    return $q->with(['getCategoryDetails:id,category']);
                }, 'getWebUserAttachment','getWebUserSubscription' => function($q){
                    return $q->whereDate('period_end' , '>=' , Carbon::now()->format('Y-m-d'));
                }, 'role_rel'])->first();
                
                // Check if user data was found
                if (!$data) {
                    return response()->json(['status' => false, 'message' => 'User not found'], 404);
                }

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
                $user->update(['is_INR_active' => 1]);
                $token = $this->generateAndStoreApiToken((int) $user->id);
                
                // ✅ Create session after OTP verification using 'web' guard
                auth('web')->login($user);
                
                // ✅ Ensure session is saved so Set-Cookie header is sent
                $request->session()->save();

                // ✅ Reload user with relationships using the user ID to ensure we get the correct user
                $data = User::where('id', $user->id)->where('userType', 2)->with(['getWebPersonalDetails', 'getWebBusinessDetails' => function($q){
                    return $q->with(['getCategoryDetails:id,category']);
                }, 'getWebUserAttachment','getWebUserSubscription' => function($q){
                    return $q->whereDate('period_end' , '>=' , Carbon::now()->format('Y-m-d'));
                }, 'role_rel'])->first();
                
                // Check if user data was found
                if (!$data) {
                    return response()->json(['status' => false, 'message' => 'User not found'], 404);
                }
                
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
        if ($request->has('personal_details')) {
            $personalDetails = $request->personal_details;

            $name = $personalDetails['firstname'].' '.$personalDetails['lastname'];
            $email = $personalDetails['email'];
            $userEmailForMail = $personalDetails['email'];

            // Prevent duplicate web-user email during basic details save.
            if (!empty($email)) {
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

            User::where('id' , $user_id)->update(['name' => $name,'email' => $email]);

            if (array_key_exists('avatar', $personalDetails)) {
                $basePath = public_path('webPortal/' . $user_id . '/attachments/avatar');
                $file = $this->uploadAttachments($personalDetails['avatar'], $basePath, ['jpeg', 'jpg', 'png']);
                if ($file === false) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Avatar must be jpeg, jpg, or png.',
                    ], 422);
                }
                $personalDetails['avatar'] = $file;
            }

            $personalDetails['user_id'] = $request['user_id'];
            if( $request->has('role') ){
                User::where('id' ,$user_id)->update(['role' => $request->role]);
            }
            WebPersonalDetails::updateOrCreate(['user_id' => $user_id], $personalDetails);
        }

        if ($request->has('business_details')) {
            $businessDetails = $request->business_details;
            $businessDetails['user_id'] = $request['user_id'];
            WebBusinessDetails::updateOrCreate(['user_id' => $user_id], $businessDetails);

            if( $request->has('role') && $request->role == 11 ) {
                $vendorDetails = [
                    'user_id' => $user_id,
                    'type' => $request['vendorDetails']['type']??'--',
                    'key' => $request['vendorDetails']['key']??'--',
                    'value' => $request['vendorDetails']['value']??'--',
                    'remarks' => $request['vendorDetails']['remarks']??'--',
                    'status' => 1
                ];
                VendorUserMap::create($vendorDetails);
            }

            if( $request->has('role') && $request->role == 12 ) { 
               $serviceProviderDetails = [
                    'user_id' => $user_id,
                    'type' => $request['serviceProviderDetails']['type']??'--',
                    'key' => $request['serviceProviderDetails']['key']??'--',
                    'value' => $request['serviceProviderDetails']['value']??'--',
                    'remarks' => $request['serviceProviderDetails']['remarks']??'--',
                    'status' => 1
                ];
                ServiceProviderUserMap::create($serviceProviderDetails);
            }
        }

        if (($panFile = $this->portalDocumentsUploadedFile($request, 'pan_file')) !== null) {
            $basePath = public_path('webPortal/' . $user_id . '/attachments/pan');
            $file = $this->uploadAttachments($panFile, $basePath, ['jpeg', 'jpg', 'png', 'pdf']);
            if ($file === false) {
                return response()->json(['status' => false, 'message' => 'PAN file must be jpeg, jpg, png, or pdf.'], 422);
            }
            WebUserAttachment::updateOrCreate(['user_id' => $user_id], ['panCard' => $file]);
        }

        if (($farmerFile = $this->portalDocumentsUploadedFile($request, 'farmer_file')) !== null) {
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


        if( $request->has('account_type') ){
            if( $request->account_type == 'new' ) {
                $mailTo = 'info@sntcgroup.com';
                $mailMessage = '';
                $subject = 'User update the profile';
                $mailFrom = 'info@sntcgroup.com';
                $mailFromName = 'SNTC Team - India';


                $data = ['userEmail' => $userEmailForMail];

                $respose = Mail::send('mail.userUpdateProfile', $data, function ($message) use ($mailTo, $mailMessage, $subject, $mailFrom, $mailFromName) {
                    $message->to($mailTo, $mailMessage)->subject($subject);
                    $message->from($mailFrom, $mailFromName);
                });
            }
        }


        return response()->json(['status' => true, 'message' => 'user details added successfully', 'data' => ['personalDetails' => $personalDetails, 'businessDetails' => $businessDetails]], 200);
    }

    public function getUserDetails($userId)
    {
        if ($userId != null) {
            $user = User::where('id', $userId)->where('userType', 2)->with(['getWebPersonalDetails', 'getWebBusinessDetails' => function($q){
                return $q->with(['cityRel:id,city_name' , 'stateRel:id,state_name', 'getCategoryDetails:id,category' , 'getBagVendorWeb:id,category']);
            }, 'getWebUserAttachment','getWebUserSubscription.planRel','role_rel'])->first();

            if (!$user) {
                return response()->json(['status' => false, 'message' => 'User not found', 'data' => []], 404);
            }

            $user = $user->toArray();

            // if( $user['role'] == 12 ){
            //     $user['get_web_business_details']['get_category_details'] =  $user['get_web_business_details']['get_bag_vendor_web'];
            // }
            if (isset($user['get_web_business_details']['get_bag_vendor_web'])) {
                unset($user['get_web_business_details']['get_bag_vendor_web']);
            }

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
        $webKeys = WebPlanKeysModel::select(["id","key","status"])
            ->where(['status'  =>  1])
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

        $webKeys = WebPlanKeysModel::select(['id', 'key', 'status'])
            ->where('status', 1)
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
            'order_id' => $order['id'],
            'amount' => $amount,
            'currency' => $currency,
        ]);
    }


    public function webVerifyPayment(Request $request)
    {
        $razorpayPaymentId = $request->razorpay_payment_id;
        $razorpayOrderId   = $request->razorpay_order_id;
        $razorpaySignature = $request->razorpay_signature;
        $userId            = $request->user_id;
        $planId            = $request->plan_id;

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
            $addedDays = $this->getSubscriptionAddedDays((string) $request->subscription_type);

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
                'subscription_type' => $request->subscription_type,
                'status' => 1
            ]);

            $totalAvailableDays = $this->getTotalAvailableSubscriptionDays((int) $userId);

            $userDetails = User::where(['id' => $userId])->first();

            if( $request->subscription_type =='trial' ){
                $webUserAttachment = WebUserAttachment::where(['user_id' => $userId])->first();

                if ($webUserAttachment === null || ! $webUserAttachment->trialDocumentsComplete()) {
                    User::where(['id' => $userId])->update(['has_validation' => "Please submit your documents to complete your profile."]);
                }else{
                    User::where(['id' => $userId])->update(['has_validation' => "Your profile is under review. We will notify you once approved."]);
                }
                

                $userName = (string) ($userDetails->name ?? '');
                $userEmail = (string) ($userDetails->email ?? '');

                Mail::to($userDetails->email)->queue(
                    new WebTrialActivatedUserMail($userName, $userEmail)
                );

                Mail::to('info@sntcgroup.com')->queue(
                    new NewUserRegistrationAdminMail($userName, $userEmail)
                );
                
            }else{
                
                $mailTo = $userDetails->email;
                $mailMessage = '';
                $subject = 'Subscription Activated – Welcome to SNTC';
                $mailFrom = 'info@sntcgroup.com';
                $mailFromName = 'SNTC Team - India';
                
                $data = ['userName' => $userDetails->name , 'userEmail' => $userDetails->email];
                $respose = Mail::send('mail.AccrountActiveWebMail', $data, function ($message) use ($mailTo, $mailMessage, $subject, $mailFrom, $mailFromName) {
                    $message->to($mailTo, $mailMessage)->subject($subject);
                    $message->from($mailFrom, $mailFromName);
                });
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
        $livePricesCount = LivePrice::whereDate('created_at' ,  $todayDate)->count();
        $paddyMandiCount = PaddyPrice::whereDate('created_at' , $todayDate)->count();
        $tradeCount = TradeQueriesINR::whereDate('created_at' , $todayDate)->count();

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

        $allowedFrom = config('portal.api_token_user_from', ['web']);
        $allowNullFrom = (bool) config('portal.api_token_allow_null_user_from', true);

        $user = User::where('api_token', $token)
            ->where('userType', 2)
            ->where(function ($query) use ($allowedFrom, $allowNullFrom) {
                $query->whereIn('user_from', $allowedFrom);
                if ($allowNullFrom) {
                    $query->orWhereNull('user_from')->orWhere('user_from', '');
                }
            })
            ->first();

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
            ->with([
                'getWebPersonalDetails',
                'getWebBusinessDetails' => function ($q) {
                    return $q->with(['getCategoryDetails:id,category']);
                },
                'getWebUserAttachment',
                'getWebUserSubscription' => function ($q) {
                    return $q->whereDate('period_end', '>=', Carbon::now()->format('Y-m-d'));
                },
                'role_rel',
            ])
            ->first();

        if (! $data) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

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
        auth('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $response = response()->json([
            'status' => true,
            'message' => 'Logged out successfully',
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
                'years' => $yearsAccess
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

}
