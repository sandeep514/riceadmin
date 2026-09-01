<?php

namespace App\Http\Controllers;

use App\Packing;
use App\PackingType;
use App\Services\VendorProductAdminNotificationService;
use App\WebBusinessDetails;
use App\WebRiceBagProduct;
use App\WebRiceBagProductPackingSize;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Session;

class WebRiceBagProductController extends Controller
{
    public function create(Request $request)
    {
        $this->normalizeIncomingPayload($request);

        if ($denied = $this->denyIfUserMismatch($request)) {
            return $denied;
        }

        $validator = Validator::make($request->all(), $this->createRules());
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
                'received_keys' => array_values(array_keys($request->except(['packing_sizes']))),
            ], 422);
        }

        $product = DB::transaction(function () use ($request) {
            $attrs = $this->payloadToAttributes($request);
            $attrs['status'] = 0;

            $product = WebRiceBagProduct::create($attrs);
            $this->syncPackingSizes($product, $request->input('packingSizes', []), $request, replace: true);

            return $product->load(['packingSizes']);
        });

        VendorProductAdminNotificationService::notify(
            'rice_bag',
            VendorProductAdminNotificationService::ACTION_CREATED,
            $product,
            $product->packingSizes
        );

        return response()->json([
            'status' => true,
            'message' => 'Rice bag product saved successfully.',
            'data' => $this->serializeProduct($product),
            'imageBasePath' => $this->imageBasePath((int) $product->user_id),
        ], 200);
    }

    public function update(Request $request)
    {
        $this->normalizeIncomingPayload($request);

        if ($denied = $this->denyIfUserMismatch($request)) {
            return $denied;
        }

        $productId = $request->input('id');
        if ($productId === null || $productId === '' || (int) $productId <= 0) {
            return $this->create($request);
        }

        $validator = Validator::make($request->all(), $this->updateRules());
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'received_keys' => array_values(array_keys($request->except(['packing_sizes']))),
            ], 422);
        }

        $product = WebRiceBagProduct::with(['packingSizes'])->find((int) $request->input('id'));
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => 'Rice bag product not found.',
            ], 404);
        }

        $authUser = $request->user();
        if ($authUser && (int) $product->user_id !== (int) $authUser->id) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden: You are not allowed to update this product.',
            ], 403);
        }

        if ($request->filled('user_id') && (int) $request->input('user_id') !== (int) $product->user_id) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden: Product does not belong to this user.',
            ], 403);
        }

        $previousPackingSizes = $product->packingSizes->map(function ($row) {
            return [
                'packing_size_id' => $row->packing_size_id,
                'packing_size' => $row->packing_size,
            ];
        });

        $product = DB::transaction(function () use ($request, $product) {
            $attrs = $this->payloadToAttributes($request, partial: true);
            if (! empty($attrs)) {
                $product->fill($attrs);
                $product->save();
            }

            if ($request->exists('packingSizes')) {
                $this->syncPackingSizes($product, $request->input('packingSizes', []), $request, replace: true);
            }

            return $product->load(['packingSizes']);
        });

        if ($request->exists('packingSizes')
            && VendorProductAdminNotificationService::hasNewPackingVariants($previousPackingSizes, $product->packingSizes)
        ) {
            VendorProductAdminNotificationService::notify(
                'rice_bag',
                VendorProductAdminNotificationService::ACTION_VARIANTS_ADDED,
                $product,
                $product->packingSizes
            );
        }

        return response()->json([
            'status' => true,
            'message' => 'Rice bag product updated successfully.',
            'data' => $this->serializeProduct($product),
            'imageBasePath' => $this->imageBasePath((int) $product->user_id),
        ], 200);
    }

    public function listByUser(Request $request, $userId)
    {
        $products = WebRiceBagProduct::with(['packingSizes'])
            ->where('user_id', (int) $userId)
            ->orderByDesc('id')
            ->get()
            ->map(function (WebRiceBagProduct $product) {
                return $this->serializeProduct($product);
            })
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Rice bag products fetched successfully.',
            'data' => $products,
            'imageBasePath' => $this->imageBasePath((int) $userId),
        ], 200);
    }

    /**
     * Public vendor products by web_business_details.id (same `id` as vendor list).
     * Returns verified products with packing size variants.
     */
    public function listByVendorId(Request $request, $id)
    {
        $vendorId = (int) $id;
        if ($vendorId <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Vendor id is required.',
            ], 422);
        }

        $vendorQuery = WebBusinessDetails::query()
            ->select(['id', 'user_id', 'company_name', 'product', 'contactPerson', 'contactMobile', 'address', 'is_sntc_recommended', 'is_active_listing']);

        // Primary: id from api/web/vendor/list/{vendorType}
        $vendor = (clone $vendorQuery)
            ->where('id', $vendorId)
            ->where('is_active_listing', 1)
            ->first();

        // Fallback: older clients may still send user_id as {id}
        if ($vendor === null) {
            $vendor = (clone $vendorQuery)
                ->where('user_id', $vendorId)
                ->where('is_active_listing', 1)
                ->orderByDesc('id')
                ->first();
        }

        if ($vendor === null) {
            return response()->json([
                'status' => false,
                'message' => 'Vendor not found.',
            ], 404);
        }

        $userId = (int) ($vendor->user_id ?? 0);
        $vendorPayload = [
            'id' => (int) $vendor->id,
            'company_name' => $vendor->company_name,
            'product' => $vendor->product,
            'contactPerson' => $vendor->contactPerson,
            'contactMobile' => $vendor->contactMobile,
            'address' => $vendor->address,
            'recommended' => (int) ($vendor->is_sntc_recommended ?? 0),
            'has_products' => false,
        ];

        if ($userId <= 0) {
            return response()->json([
                'status' => true,
                'message' => 'Vendor products fetched successfully.',
                'vendor' => $vendorPayload,
                'data' => [],
                'imageBasePath' => null,
            ], 200);
        }

        $products = WebRiceBagProduct::with(['packingSizes'])
            ->where('user_id', $userId)
            ->where('status', 1)
            ->whereHas('packingSizes')
            ->orderByDesc('id')
            ->get();

        $bagTypeIds = $products->pluck('bag_type_id')->filter()->unique()->values()->all();
        $bagTypes = $bagTypeIds === []
            ? collect()
            : PackingType::whereIn('id', $bagTypeIds)->pluck('name', 'id');

        $data = $products->map(function (WebRiceBagProduct $product) use ($bagTypes) {
            return $this->serializeVendorProduct($product, $bagTypes);
        })->values();

        $vendorPayload['has_products'] = $data->isNotEmpty();

        return response()->json([
            'status' => true,
            'message' => 'Vendor products fetched successfully.',
            'vendor' => $vendorPayload,
            'data' => $data,
            'imageBasePath' => $this->imageBasePath($userId),
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $product = WebRiceBagProduct::with(['packingSizes'])->find((int) $id);
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => 'Rice bag product not found.',
            ], 404);
        }

        $authUser = $request->user();
        if ($authUser && (int) $product->user_id !== (int) $authUser->id) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden: You are not allowed to access this product.',
            ], 403);
        }

        return response()->json([
            'status' => true,
            'message' => 'Rice bag product fetched successfully.',
            'data' => $this->serializeProduct($product),
            'imageBasePath' => $this->imageBasePath((int) $product->user_id),
        ], 200);
    }

    public function delete(Request $request, $id)
    {
        $product = WebRiceBagProduct::with('packingSizes')->find((int) $id);
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => 'Rice bag product not found.',
            ], 404);
        }

        $authUser = $request->user();
        if ($authUser && (int) $product->user_id !== (int) $authUser->id) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden: You are not allowed to delete this product.',
            ], 403);
        }

        $basePath = public_path($this->imageBasePath((int) $product->user_id));
        foreach ($product->packingSizes as $size) {
            if ($size->image) {
                $filePath = $basePath . '/' . $size->image;
                if (is_file($filePath)) {
                    @unlink($filePath);
                }
            }
        }

        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Rice bag product deleted successfully.',
        ], 200);
    }

    /** Delete image for a packing size row (route param is packing_sizes.id). */
    public function deleteImage(Request $request, $imageId)
    {
        $size = WebRiceBagProductPackingSize::find((int) $imageId);
        if ($size === null) {
            return response()->json([
                'status' => false,
                'message' => 'Packing size image not found.',
            ], 404);
        }

        $product = WebRiceBagProduct::find($size->product_id);
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => 'Rice bag product not found.',
            ], 404);
        }

        $authUser = $request->user();
        if ($authUser && (int) $product->user_id !== (int) $authUser->id) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden: You are not allowed to delete this image.',
            ], 403);
        }

        if ($size->image) {
            $filePath = public_path($this->imageBasePath((int) $product->user_id) . '/' . $size->image);
            if (is_file($filePath)) {
                @unlink($filePath);
            }
            $size->update(['image' => null]);
        }

        $product->load(['packingSizes']);

        return response()->json([
            'status' => true,
            'message' => 'Image deleted successfully.',
            'data' => $this->serializeProduct($product),
            'imageBasePath' => $this->imageBasePath((int) $product->user_id),
        ], 200);
    }

    public function showProductsToAdmin()
    {
        $products = WebRiceBagProduct::with(['user:id,name,email,mobile', 'packingSizes'])
            ->orderByDesc('id')
            ->get();

        $bagTypes = PackingType::pluck('name', 'id');

        return view('webRiceBagProducts.list', compact('products', 'bagTypes'));
    }

    public function showProductToAdmin($id)
    {
        $product = WebRiceBagProduct::with([
            'user:id,name,email,mobile',
            'packingSizes',
        ])->findOrFail((int) $id);

        $bagTypes = PackingType::pluck('name', 'id');
        $imageBasePath = $this->imageBasePath((int) $product->user_id);

        return view('webRiceBagProducts.show', compact('product', 'bagTypes', 'imageBasePath'));
    }

    public function toggleWebRiceBagProductStatus($id)
    {
        $product = WebRiceBagProduct::find((int) $id);

        if ($product === null) {
            Session::flash('error', 'Error|Rice bag product not found.');
            return back();
        }

        $product->update(['status' => (int) $product->status === 1 ? 0 : 1]);
        Session::flash('success', 'Success|Status updated successfully.');

        return back();
    }

    private function createRules(): array
    {
        $packingForms = array_values(Packing::$packingForms);

        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'bag_type_id' => ['required', 'integer'],
            'specification' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'additional_information' => ['nullable', 'string'],
            'packing_form_id' => ['required', 'integer'],
            'packing_form' => ['required', 'string', 'in:' . implode(',', $packingForms)],
            'packingSizes' => ['required', 'array', 'min:1'],
            'packingSizes.*.packingSizeId' => ['required', 'integer'],
            'packingSizes.*.packingSize' => ['nullable', 'string', 'max:255'],
            'packingSizes.*.rate' => ['required', 'numeric'],
            'packingSizes.*.gst' => ['nullable', 'numeric'],
            'packingSizes.*.totalPrice' => ['nullable', 'numeric'],
            'packingSizes.*.bagSize' => ['nullable', 'string', 'max:255'],
            'packingSizes.*.bagWeight' => ['nullable', 'string', 'max:64'],
        ];
    }

    private function updateRules(): array
    {
        $packingForms = array_values(Packing::$packingForms);

        return [
            'id' => ['required', 'integer', 'exists:web_rice_bag_products,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'bag_type_id' => ['sometimes', 'required', 'integer'],
            'specification' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'additional_information' => ['nullable', 'string'],
            'packing_form_id' => ['sometimes', 'required', 'integer'],
            'packing_form' => ['sometimes', 'required', 'string', 'in:' . implode(',', $packingForms)],
            'packingSizes' => ['sometimes', 'array', 'min:1'],
            'packingSizes.*.packingSizeId' => ['required', 'integer'],
            'packingSizes.*.packingSize' => ['nullable', 'string', 'max:255'],
            'packingSizes.*.rate' => ['required', 'numeric'],
            'packingSizes.*.gst' => ['nullable', 'numeric'],
            'packingSizes.*.totalPrice' => ['nullable', 'numeric'],
            'packingSizes.*.bagSize' => ['nullable', 'string', 'max:255'],
            'packingSizes.*.bagWeight' => ['nullable', 'string', 'max:64'],
        ];
    }

    private function normalizeIncomingPayload(Request $request): void
    {
        foreach (['payload', 'data', 'body', 'json'] as $wrapKey) {
            if (! $request->exists($wrapKey)) {
                continue;
            }
            $wrapped = $request->input($wrapKey);
            if (is_string($wrapped) && trim($wrapped) !== '') {
                $decoded = json_decode($wrapped, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $request->merge($decoded);
                }
            } elseif (is_array($wrapped)) {
                $request->merge($wrapped);
            }
        }

        if (! $request->filled('bag_type_id') && ! $request->filled('bagTypeId')) {
            $raw = $request->getContent();
            if (is_string($raw) && $raw !== '') {
                $trimmed = ltrim($raw);
                if ($trimmed !== '' && ($trimmed[0] === '{' || $trimmed[0] === '[')) {
                    $decoded = json_decode($raw, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        if (array_is_list($decoded) && isset($decoded[0]) && is_array($decoded[0])) {
                            $request->merge($decoded[0]);
                        } else {
                            $request->merge($decoded);
                        }
                    }
                }
            }
        }

        $aliases = [
            'bagTypeId' => 'bag_type_id',
            'additionalInformation' => 'additional_information',
            'packing_sizes' => 'packingSizes',
        ];
        foreach ($aliases as $from => $to) {
            if ($request->exists($from) && ! $request->exists($to)) {
                $request->merge([$to => $request->input($from)]);
            }
        }

        if ($request->filled('userId') && ! $request->filled('user_id')) {
            $request->merge(['user_id' => $request->input('userId')]);
        }

        if ($request->has('id') && ($request->input('id') === '' || $request->input('id') === null)) {
            $request->request->remove('id');
        }

        // One product = one packing form. Frontend may send packing_forms[] / packing_form_ids[] — use first value.
        $formId = $request->input('packing_form_id', $request->input('packingFormId'));
        $formLabel = $request->input('packing_form', $request->input('packingForm'));

        $formIds = $request->input('packing_form_ids', $request->input('packingFormIds'));
        $formLabels = $request->input('packing_forms', $request->input('packingForms'));
        if (is_array($formIds) && $formIds !== []) {
            $formId = $formIds[0];
        }
        if (is_array($formLabels) && $formLabels !== []) {
            $formLabel = $formLabels[0];
        }

        if (is_numeric($formLabel) && isset(Packing::$packingForms[(int) $formLabel])) {
            $formId = (int) $formLabel;
            $formLabel = Packing::$packingForms[$formId];
        } elseif (is_numeric($formId) && ($formLabel === null || $formLabel === '') && isset(Packing::$packingForms[(int) $formId])) {
            $formLabel = Packing::$packingForms[(int) $formId];
        }

        if ($formId !== null && $formId !== '') {
            $request->merge(['packing_form_id' => (int) $formId]);
        }
        if ($formLabel !== null && $formLabel !== '') {
            $request->merge(['packing_form' => $formLabel]);
        }

        $packingSizes = $request->input('packingSizes', $request->input('packing_sizes'));
        if (is_string($packingSizes) && trim($packingSizes) !== '') {
            $decoded = json_decode($packingSizes, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $packingSizes = $decoded;
            }
        }

        if (is_array($packingSizes) && ! empty($packingSizes)) {
            $normalizedRows = [];
            foreach ($packingSizes as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $existingImage = $row['existingImage']
                    ?? $row['existing_image']
                    ?? $row['image']
                    ?? $row['image_name']
                    ?? null;
                if (is_string($existingImage) && $existingImage !== '') {
                    // Frontend may send full URL — keep only filename.
                    $existingImage = basename(parse_url($existingImage, PHP_URL_PATH) ?: $existingImage);
                    if ($existingImage === '' || str_contains($existingImage, ' ')) {
                        $existingImage = null;
                    }
                } else {
                    $existingImage = null;
                }

                $normalizedRows[] = [
                    'id' => $row['id'] ?? $row['packing_size_row_id'] ?? null,
                    'packingSizeId' => $row['packingSizeId'] ?? $row['packing_size_id'] ?? null,
                    'packingSize' => $row['packingSize'] ?? $row['packing_size'] ?? null,
                    'rate' => $row['rate'] ?? null,
                    'gst' => $row['gst'] ?? null,
                    'totalPrice' => $row['totalPrice'] ?? $row['total_price'] ?? null,
                    'bagSize' => $row['bagSize'] ?? $row['bag_size'] ?? null,
                    'bagWeight' => $row['bagWeight'] ?? $row['bag_weight'] ?? null,
                    'existingImage' => $existingImage,
                    '_index' => is_numeric($index) ? (int) $index : null,
                ];
            }
            $request->merge(['packingSizes' => $normalizedRows]);
        }
    }

    private function denyIfUserMismatch(Request $request)
    {
        $authUser = $request->user();
        if (! $authUser) {
            return null;
        }

        $payloadUserId = $request->input('user_id', $request->input('userId'));
        if ($payloadUserId !== null && $payloadUserId !== '' && (int) $payloadUserId !== (int) $authUser->id) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden: You are not allowed to perform this action for another user.',
            ], 403);
        }

        return null;
    }

    private function payloadToAttributes(Request $request, bool $partial = false): array
    {
        $map = [
            'bag_type_id' => 'bag_type_id',
            'specification' => 'specification',
            'description' => 'description',
            'additional_information' => 'additional_information',
            'packing_form_id' => 'packing_form_id',
            'packing_form' => 'packing_form',
        ];

        $attrs = [];

        if ((! $partial || $request->filled('user_id')) && $request->filled('user_id')) {
            $attrs['user_id'] = (int) $request->input('user_id');
        }

        foreach ($map as $inputKey => $column) {
            if ($partial && ! $request->exists($inputKey)) {
                continue;
            }
            if (! $request->exists($inputKey)) {
                continue;
            }

            $value = $request->input($inputKey);
            if (in_array($column, ['bag_type_id', 'packing_form_id'], true)) {
                $attrs[$column] = $value === null || $value === '' ? null : (int) $value;
            } else {
                $attrs[$column] = $value;
            }
        }

        return $attrs;
    }

    private function syncPackingSizes(WebRiceBagProduct $product, $packingSizes, Request $request, bool $replace = true): void
    {
        if (! is_array($packingSizes)) {
            $packingSizes = [];
        }

        $basePath = public_path($this->imageBasePath((int) $product->user_id));
        $existingRows = WebRiceBagProductPackingSize::where('product_id', $product->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $existingById = $existingRows->keyBy('id');
        $existingByPackingSizeId = $existingRows
            ->filter(fn ($row) => $row->packing_size_id !== null)
            ->keyBy(fn ($row) => (string) $row->packing_size_id);
        $existingByIndex = $existingRows->values();

        $keptImageNames = [];
        $sortOrder = 0;
        $createdIds = [];

        foreach ($packingSizes as $row) {
            if (! is_array($row)) {
                continue;
            }

            $sortOrder++;
            $index = $row['_index'] ?? ($sortOrder - 1);

            $matched = null;
            if (! empty($row['id']) && $existingById->has((int) $row['id'])) {
                $matched = $existingById->get((int) $row['id']);
            } elseif (! empty($row['packingSizeId']) && $existingByPackingSizeId->has((string) $row['packingSizeId'])) {
                $matched = $existingByPackingSizeId->get((string) $row['packingSizeId']);
            } elseif ($existingByIndex->has($index)) {
                $matched = $existingByIndex->get($index);
            }

            $previousImage = $matched?->image;
            $imageName = $this->resolvePackingSizeImage(
                $request,
                $product,
                $index,
                $row,
                $previousImage
            );

            if ($imageName) {
                $keptImageNames[] = $imageName;
            }

            // If a new file replaced an old one, remove the old file.
            if ($previousImage
                && $imageName
                && $previousImage !== $imageName
            ) {
                $oldPath = $basePath . '/' . $previousImage;
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $created = WebRiceBagProductPackingSize::create([
                'product_id' => $product->id,
                'packing_size_id' => isset($row['packingSizeId']) && $row['packingSizeId'] !== ''
                    ? (int) $row['packingSizeId']
                    : null,
                'packing_size' => $row['packingSize'] ?? null,
                'rate' => $row['rate'] ?? null,
                'gst' => $row['gst'] ?? null,
                'total_price' => $row['totalPrice'] ?? null,
                'bag_size' => $row['bagSize'] ?? null,
                'bag_weight' => $row['bagWeight'] ?? null,
                'image' => $imageName,
                'sort_order' => $sortOrder,
            ]);
            $createdIds[] = $created->id;
        }

        if ($replace) {
            // Delete old DB rows (not the newly created ones).
            WebRiceBagProductPackingSize::where('product_id', $product->id)
                ->whereNotIn('id', $createdIds)
                ->delete();

            // Delete only image files that are no longer referenced.
            foreach ($existingRows as $old) {
                if (! $old->image || in_array($old->image, $keptImageNames, true)) {
                    continue;
                }
                $filePath = $basePath . '/' . $old->image;
                if (is_file($filePath)) {
                    @unlink($filePath);
                }
            }
        }
    }

    private function resolvePackingSizeImage(
        Request $request,
        WebRiceBagProduct $product,
        $index,
        array $row,
        ?string $previousImage = null
    ): ?string {
        $file = $request->file("packing_sizes.{$index}.image")
            ?? $request->file("packingSizes.{$index}.image");

        if ($file instanceof UploadedFile && $file->isValid()) {
            return $this->storePackingSizeImage($product, $file);
        }

        $existing = $row['existingImage'] ?? null;
        if (is_string($existing) && $existing !== '') {
            $filename = basename(parse_url($existing, PHP_URL_PATH) ?: $existing);
            if ($filename !== '' && is_file(public_path($this->imageBasePath((int) $product->user_id) . '/' . $filename))) {
                return $filename;
            }
            // Still accept known previous filename even if path check fails (moved dirs etc.)
            if ($filename !== '' && ! str_contains($filename, '/')) {
                return $filename;
            }
        }

        // Keep previous image when frontend did not send a new file.
        if (is_string($previousImage) && $previousImage !== '') {
            return $previousImage;
        }

        return null;
    }

    private function storePackingSizeImage(WebRiceBagProduct $product, UploadedFile $file): string
    {
        $dir = public_path($this->imageBasePath((int) $product->user_id));
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $newName = time() . '_' . uniqid('', true) . '.' . $ext;
        $file->move($dir, $newName);

        return $newName;
    }

    private function imageBasePath(int $userId): string
    {
        return 'uploads/rice-bag-products/' . $userId;
    }

    private function serializeProduct(WebRiceBagProduct $product): array
    {
        $basePath = $this->imageBasePath((int) $product->user_id);

        $packingSizes = $product->packingSizes->map(function (WebRiceBagProductPackingSize $size) use ($basePath) {
            return [
                'id' => (int) $size->id,
                'packingSizeId' => $size->packing_size_id !== null ? (int) $size->packing_size_id : null,
                'packingSize' => $size->packing_size,
                'rate' => $size->rate !== null ? (string) $size->rate : null,
                'gst' => $size->gst !== null ? (string) $size->gst : null,
                'totalPrice' => $size->total_price !== null ? (string) $size->total_price : null,
                'bagSize' => $size->bag_size,
                'bagWeight' => $size->bag_weight,
                'image' => $size->image,
                'imageUrl' => $size->image ? asset($basePath . '/' . $size->image) : null,
                'sortOrder' => (int) $size->sort_order,
            ];
        })->values()->all();

        return [
            'id' => (int) $product->id,
            'userId' => (int) $product->user_id,
            'bagTypeId' => $product->bag_type_id !== null ? (int) $product->bag_type_id : null,
            'specification' => $product->specification,
            'description' => $product->description,
            'additionalInformation' => $product->additional_information,
            'packingFormId' => $product->packing_form_id !== null ? (int) $product->packing_form_id : null,
            'packingForm' => $product->packing_form,
            'status' => (int) $product->status,
            'packingSizes' => $packingSizes,
        ];
    }

    /** Public vendor catalog payload — packing sizes exposed as variants. */
    private function serializeVendorProduct(WebRiceBagProduct $product, $bagTypes = null): array
    {
        $basePath = $this->imageBasePath((int) $product->user_id);

        $variants = $product->packingSizes->map(function (WebRiceBagProductPackingSize $size) use ($basePath) {
            return [
                'id' => (int) $size->id,
                'packingSizeId' => $size->packing_size_id !== null ? (int) $size->packing_size_id : null,
                'packingSize' => $size->packing_size,
                'rate' => $size->rate !== null ? (string) $size->rate : null,
                'gst' => $size->gst !== null ? (string) $size->gst : null,
                'totalPrice' => $size->total_price !== null ? (string) $size->total_price : null,
                'bagSize' => $size->bag_size,
                'bagWeight' => $size->bag_weight,
                'image' => $size->image,
                'imageUrl' => $size->image ? asset($basePath . '/' . $size->image) : null,
                'sortOrder' => (int) $size->sort_order,
            ];
        })->values()->all();

        $bagTypeName = null;
        if ($product->bag_type_id !== null) {
            if ($bagTypes !== null) {
                $bagTypeName = $bagTypes[$product->bag_type_id] ?? null;
            } else {
                $bagTypeName = optional(PackingType::find($product->bag_type_id))->name;
            }
        }

        return [
            'id' => (int) $product->id,
            'bagTypeId' => $product->bag_type_id !== null ? (int) $product->bag_type_id : null,
            'bagTypeName' => $bagTypeName,
            'specification' => $product->specification,
            'description' => $product->description,
            'additionalInformation' => $product->additional_information,
            'packingFormId' => $product->packing_form_id !== null ? (int) $product->packing_form_id : null,
            'packingForm' => $product->packing_form,
            'variants' => $variants,
        ];
    }
}
