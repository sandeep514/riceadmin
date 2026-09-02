<?php

namespace App\Services;

use App\CartoonType;
use App\CylinderType;
use App\Support\VendorOtherOption;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WebVendorPackagingProductService
{
    /** @var class-string<Model> */
    private string $productModel;

    /** @var class-string<Model> */
    private string $variantModel;

    /** @var class-string<Model> */
    private string $typeModel;

    private string $productsTable;

    private string $typeIdColumn;

    private string $typeIdCamel;

    private string $typeNameCamel;

    private string $label;

    private string $uploadFolder;

    public static function cartoon(): self
    {
        return new self(
            productModel: \App\WebCartoonProduct::class,
            variantModel: \App\WebCartoonProductVariant::class,
            typeModel: CartoonType::class,
            productsTable: 'web_cartoon_products',
            typeIdColumn: 'cartoon_type_id',
            typeIdCamel: 'cartoonTypeId',
            typeNameCamel: 'cartoonTypeName',
            label: 'Cartoon',
            uploadFolder: 'cartoon-products',
        );
    }

    public static function cylinder(): self
    {
        return new self(
            productModel: \App\WebCylinderProduct::class,
            variantModel: \App\WebCylinderProductVariant::class,
            typeModel: CylinderType::class,
            productsTable: 'web_cylinder_products',
            typeIdColumn: 'cylinder_type_id',
            typeIdCamel: 'cylinderTypeId',
            typeNameCamel: 'cylinderTypeName',
            label: 'Cylinder',
            uploadFolder: 'cylinder-products',
        );
    }

    /**
     * @param  class-string<Model>  $productModel
     * @param  class-string<Model>  $variantModel
     * @param  class-string<Model>  $typeModel
     */
    private function __construct(
        string $productModel,
        string $variantModel,
        string $typeModel,
        string $productsTable,
        string $typeIdColumn,
        string $typeIdCamel,
        string $typeNameCamel,
        string $label,
        string $uploadFolder
    ) {
        $this->productModel = $productModel;
        $this->variantModel = $variantModel;
        $this->typeModel = $typeModel;
        $this->productsTable = $productsTable;
        $this->typeIdColumn = $typeIdColumn;
        $this->typeIdCamel = $typeIdCamel;
        $this->typeNameCamel = $typeNameCamel;
        $this->label = $label;
        $this->uploadFolder = $uploadFolder;
    }

    private function kindKey(): string
    {
        return $this->typeIdColumn === 'cylinder_type_id' ? 'cylinder' : 'cartoon';
    }

    public function create(Request $request)
    {
        $this->normalizeIncomingPayload($request);

        if ($denied = $this->denyIfUserMismatch($request)) {
            return $denied;
        }

        $validator = Validator::make($request->all(), $this->createRules());
        $this->validateOtherValues($validator, $request, requireType: true);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
                'received_keys' => array_values(array_keys($request->except(['variants', 'packing_sizes', 'packingSizes']))),
            ], 422);
        }

        $productModel = $this->productModel;

        $product = DB::transaction(function () use ($request, $productModel) {
            $attrs = $this->payloadToAttributes($request);
            $attrs['status'] = 0;

            /** @var Model $product */
            $product = $productModel::create($attrs);
            $this->syncVariants($product, $request->input('variants', []), $request, replace: true);

            return $product->load(['variants']);
        });

        VendorProductAdminNotificationService::notify(
            $this->kindKey(),
            VendorProductAdminNotificationService::ACTION_CREATED,
            $product,
            $product->variants
        );

        return response()->json([
            'status' => true,
            'message' => $this->label.' product saved successfully.',
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
        $this->validateOtherValues(
            $validator,
            $request,
            requireType: $request->exists($this->typeIdColumn),
            requireSizes: $request->exists('variants')
        );
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
                'received_keys' => array_values(array_keys($request->except(['variants', 'packing_sizes', 'packingSizes']))),
            ], 422);
        }

        $productModel = $this->productModel;
        $product = $productModel::with(['variants'])->find((int) $request->input('id'));
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => $this->label.' product not found.',
            ], 404);
        }

        if ($denied = $this->denyIfNotOwner($request, (int) $product->user_id, 'update')) {
            return $denied;
        }

        if ($request->filled('user_id') && (int) $request->input('user_id') !== (int) $product->user_id) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden: Product does not belong to this user.',
            ], 403);
        }

        $previousVariants = $product->variants->map(function ($row) {
            return [
                'packing_size_id' => $row->packing_size_id,
                'packing_size' => $row->packing_size,
            ];
        });

        $product = DB::transaction(function () use ($request, $product) {
            $attrs = $this->payloadToAttributes($request, partial: true);
            if ($attrs !== []) {
                $product->fill($attrs);
                $product->save();
            }

            if ($request->exists('variants')) {
                $this->syncVariants($product, $request->input('variants', []), $request, replace: true);
            }

            return $product->load(['variants']);
        });

        $action = ($request->exists('variants')
            && VendorProductAdminNotificationService::hasNewPackingVariants($previousVariants, $product->variants))
            ? VendorProductAdminNotificationService::ACTION_VARIANTS_ADDED
            : VendorProductAdminNotificationService::ACTION_UPDATED;

        VendorProductAdminNotificationService::notify(
            $this->kindKey(),
            $action,
            $product,
            $product->variants
        );

        return response()->json([
            'status' => true,
            'message' => $this->label.' product updated successfully.',
            'data' => $this->serializeProduct($product),
            'imageBasePath' => $this->imageBasePath((int) $product->user_id),
        ], 200);
    }

    public function listByUser(Request $request, $userId)
    {
        $products = $this->productModel::with(['variants'])
            ->where('user_id', (int) $userId)
            ->orderByDesc('id')
            ->get()
            ->map(fn (Model $product) => $this->serializeProduct($product))
            ->values();

        return response()->json([
            'status' => true,
            'message' => $this->label.' products fetched successfully.',
            'data' => $products,
            'imageBasePath' => $this->imageBasePath((int) $userId),
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $product = $this->productModel::with(['variants'])->find((int) $id);
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => $this->label.' product not found.',
            ], 404);
        }

        if ($denied = $this->denyIfNotOwner($request, (int) $product->user_id, 'access')) {
            return $denied;
        }

        return response()->json([
            'status' => true,
            'message' => $this->label.' product fetched successfully.',
            'data' => $this->serializeProduct($product),
            'imageBasePath' => $this->imageBasePath((int) $product->user_id),
        ], 200);
    }

    public function delete(Request $request, $id)
    {
        $product = $this->productModel::with('variants')->find((int) $id);
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => $this->label.' product not found.',
            ], 404);
        }

        if ($denied = $this->denyIfNotOwner($request, (int) $product->user_id, 'delete')) {
            return $denied;
        }

        $basePath = public_path($this->imageBasePath((int) $product->user_id));
        foreach ($product->variants as $variant) {
            if ($variant->image) {
                $filePath = $basePath.'/'.$variant->image;
                if (is_file($filePath)) {
                    @unlink($filePath);
                }
            }
        }

        $product->delete();

        return response()->json([
            'status' => true,
            'message' => $this->label.' product deleted successfully.',
        ], 200);
    }

    public function deleteImage(Request $request, $imageId)
    {
        $variant = $this->variantModel::find((int) $imageId);
        if ($variant === null) {
            return response()->json([
                'status' => false,
                'message' => 'Variant image not found.',
            ], 404);
        }

        $product = $this->productModel::find($variant->product_id);
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => $this->label.' product not found.',
            ], 404);
        }

        if ($denied = $this->denyIfNotOwner($request, (int) $product->user_id, 'delete this image')) {
            return $denied;
        }

        if ($variant->image) {
            $filePath = public_path($this->imageBasePath((int) $product->user_id).'/'.$variant->image);
            if (is_file($filePath)) {
                @unlink($filePath);
            }
            $variant->update(['image' => null]);
        }

        $product->load(['variants']);

        return response()->json([
            'status' => true,
            'message' => 'Image deleted successfully.',
            'data' => $this->serializeProduct($product),
            'imageBasePath' => $this->imageBasePath((int) $product->user_id),
        ], 200);
    }

    public function verifiedProductsForUser(int $userId)
    {
        return $this->verifiedProductsForOwners([$userId]);
    }

    /**
     * @param  array<int>  $ownerIds
     */
    public function verifiedProductsForOwners(array $ownerIds)
    {
        $products = $this->productsForOwners($ownerIds, verifiedOnly: true);
        if ($products->isNotEmpty()) {
            return $products;
        }

        return $this->productsForOwners($ownerIds, verifiedOnly: false);
    }

    /**
     * @param  array<int>  $ownerIds
     */
    public function productsForOwners(array $ownerIds, bool $verifiedOnly = true)
    {
        $ownerIds = array_values(array_unique(array_filter(array_map('intval', $ownerIds))));
        if ($ownerIds === []) {
            return $this->productModel::query()->whereRaw('1 = 0')->get();
        }

        $query = $this->productModel::with(['variants'])
            ->whereIn('user_id', $ownerIds)
            ->whereHas('variants')
            ->orderByDesc('id');

        if ($verifiedOnly) {
            $query->where('status', 1);
        }

        return $query->get();
    }

    public function serializeVendorProduct(Model $product, $types = null): array
    {
        $basePath = $this->imageBasePath((int) $product->user_id);
        $typeId = $product->{$this->typeIdColumn};

        $variants = $product->variants->map(function (Model $variant) use ($basePath) {
            return $this->serializeVariantRow($variant, $basePath);
        })->values()->all();

        $typeName = null;
        if ($typeId !== null) {
            if ($types !== null) {
                $typeName = $types[$typeId] ?? null;
            } else {
                $typeName = $this->typeModel::query()->where('id', $typeId)->value('type');
            }
        }

        return [
            'id' => (int) $product->id,
            $this->typeIdCamel => $typeId !== null ? (int) $typeId : null,
            'otherTypeValue' => $product->other_type_value,
            $this->typeNameCamel => $typeName,
            'specification' => $product->specification,
            'description' => $product->description,
            'additionalInformation' => $product->additional_information,
            'variants' => $variants,
        ];
    }

    public function imageBasePath(int $userId): string
    {
        return 'uploads/'.$this->uploadFolder.'/'.$userId;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function typeIdColumn(): string
    {
        return $this->typeIdColumn;
    }

    public function productModel(): string
    {
        return $this->productModel;
    }

    public function toggleStatus(int $id): bool
    {
        $product = $this->productModel::find($id);
        if ($product === null) {
            return false;
        }

        $wasPending = (int) $product->status !== 1;
        $newStatus = $wasPending ? 1 : 0;
        $product->update(['status' => $newStatus]);

        if ($wasPending && $newStatus === 1) {
            VendorProductAdminNotificationService::notifyAccepted($this->kindKey(), $product->fresh());
        }

        return true;
    }

    public function typeOptions(): array
    {
        return $this->typeModel::query()
            ->orderByRaw('order_no IS NULL, order_no ASC')
            ->orderBy('type')
            ->pluck('type', 'id')
            ->all();
    }

    private function createRules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            $this->typeIdColumn => ['required', 'integer', 'exists:'.(new $this->typeModel)->getTable().',id'],
            'other_type_value' => ['nullable', 'string', 'max:255'],
            'specification' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'additional_information' => ['nullable', 'string'],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.packingSizeId' => ['required', 'integer'],
            'variants.*.packingSize' => ['nullable', 'string', 'max:255'],
            'variants.*.otherSizeValue' => ['nullable', 'string', 'max:255'],
            'variants.*.rate' => ['required', 'numeric'],
            'variants.*.gst' => ['nullable', 'numeric'],
            'variants.*.totalPrice' => ['nullable', 'numeric'],
            'variants.*.bagSize' => ['nullable', 'string', 'max:255'],
            'variants.*.bagWeight' => ['nullable', 'string', 'max:64'],
        ];
    }

    private function updateRules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:'.$this->productsTable.',id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            $this->typeIdColumn => ['sometimes', 'required', 'integer', 'exists:'.(new $this->typeModel)->getTable().',id'],
            'other_type_value' => ['nullable', 'string', 'max:255'],
            'specification' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'additional_information' => ['nullable', 'string'],
            'variants' => ['sometimes', 'array', 'min:1'],
            'variants.*.packingSizeId' => ['required', 'integer'],
            'variants.*.packingSize' => ['nullable', 'string', 'max:255'],
            'variants.*.otherSizeValue' => ['nullable', 'string', 'max:255'],
            'variants.*.rate' => ['required', 'numeric'],
            'variants.*.gst' => ['nullable', 'numeric'],
            'variants.*.totalPrice' => ['nullable', 'numeric'],
            'variants.*.bagSize' => ['nullable', 'string', 'max:255'],
            'variants.*.bagWeight' => ['nullable', 'string', 'max:64'],
        ];
    }

    private function validateOtherValues($validator, Request $request, bool $requireType = true, bool $requireSizes = true): void
    {
        $kind = $this->kindKey();

        $validator->after(function ($validator) use ($request, $requireType, $requireSizes, $kind) {
            if ($requireType && VendorOtherOption::isOtherTypeId($kind, $request->input($this->typeIdColumn))) {
                if (VendorOtherOption::normalizeOtherValue($request->input('other_type_value')) === null) {
                    $validator->errors()->add(
                        'other_type_value',
                        'The other type value field is required when '.$this->label.' type is Other.'
                    );
                }
            }

            if (! $requireSizes || ! $request->exists('variants')) {
                return;
            }

            $rows = $request->input('variants', []);
            if (! is_array($rows)) {
                return;
            }

            foreach ($rows as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }

                if (VendorOtherOption::isOtherSizeId($kind, $row['packingSizeId'] ?? null)
                    && VendorOtherOption::normalizeOtherValue($row['otherSizeValue'] ?? null) === null
                ) {
                    $validator->errors()->add(
                        'variants.'.$index.'.otherSizeValue',
                        'The other size value field is required when packing size is Other.'
                    );
                }
            }
        });
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

        if (! $request->filled($this->typeIdColumn) && ! $request->filled($this->typeIdCamel)) {
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
            $this->typeIdCamel => $this->typeIdColumn,
            'otherTypeValue' => 'other_type_value',
            'additionalInformation' => 'additional_information',
            'packing_sizes' => 'variants',
            'packingSizes' => 'variants',
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

        if ($request->filled($this->typeIdColumn)) {
            $request->merge([
                'other_type_value' => VendorOtherOption::isOtherTypeId($this->kindKey(), $request->input($this->typeIdColumn))
                    ? VendorOtherOption::normalizeOtherValue($request->input('other_type_value'))
                    : null,
            ]);
        } elseif ($request->exists('other_type_value')) {
            $request->merge([
                'other_type_value' => VendorOtherOption::normalizeOtherValue($request->input('other_type_value')),
            ]);
        }

        $variants = $request->input('variants');
        if (is_string($variants) && trim($variants) !== '') {
            $decoded = json_decode($variants, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $variants = $decoded;
            }
        }

        if (is_array($variants) && $variants !== []) {
            $normalizedRows = [];
            foreach ($variants as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }

                $existingImage = $row['existingImage']
                    ?? $row['existing_image']
                    ?? $row['image']
                    ?? $row['image_name']
                    ?? null;
                if (is_string($existingImage) && $existingImage !== '') {
                    $existingImage = basename(parse_url($existingImage, PHP_URL_PATH) ?: $existingImage);
                    if ($existingImage === '' || str_contains($existingImage, ' ')) {
                        $existingImage = null;
                    }
                } else {
                    $existingImage = null;
                }

                $packingSizeId = $row['packingSizeId'] ?? $row['packing_size_id'] ?? null;
                [$packingSize, $otherSizeValue] = VendorOtherOption::resolvePackingSizeOther(
                    $this->kindKey(),
                    $packingSizeId,
                    $row
                );

                $normalizedRows[] = [
                    'id' => $row['id'] ?? $row['variant_row_id'] ?? null,
                    'packingSizeId' => $packingSizeId,
                    'packingSize' => $packingSize,
                    'otherSizeValue' => $otherSizeValue,
                    'rate' => $row['rate'] ?? null,
                    'gst' => $row['gst'] ?? null,
                    'totalPrice' => $row['totalPrice'] ?? $row['total_price'] ?? null,
                    'bagSize' => $row['bagSize'] ?? $row['bag_size'] ?? null,
                    'bagWeight' => $row['bagWeight'] ?? $row['bag_weight'] ?? null,
                    'existingImage' => $existingImage,
                    '_index' => is_numeric($index) ? (int) $index : null,
                ];
            }
            $request->merge(['variants' => $normalizedRows]);
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

    private function denyIfNotOwner(Request $request, int $ownerUserId, string $action)
    {
        $authUser = $request->user();
        if ($authUser && (int) $ownerUserId !== (int) $authUser->id) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden: You are not allowed to '.$action.' this product.',
            ], 403);
        }

        return null;
    }

    private function payloadToAttributes(Request $request, bool $partial = false): array
    {
        $map = [
            $this->typeIdColumn => $this->typeIdColumn,
            'other_type_value' => 'other_type_value',
            'specification' => 'specification',
            'description' => 'description',
            'additional_information' => 'additional_information',
        ];

        $attrs = [];

        if ((! $partial || $request->filled('user_id')) && $request->filled('user_id')) {
            $attrs['user_id'] = (int) $request->input('user_id');
        }

        foreach ($map as $inputKey => $column) {
            if ($partial && ! $request->exists($inputKey) && $column !== 'other_type_value') {
                continue;
            }
            if (! $request->exists($inputKey) && $column !== 'other_type_value') {
                continue;
            }

            $value = $request->input($inputKey);
            if ($column === $this->typeIdColumn) {
                $attrs[$column] = $value === null || $value === '' ? null : (int) $value;
            } elseif ($column === 'other_type_value') {
                // Resolve only when type id is present so partial updates don't wipe it.
                if ($request->exists($this->typeIdColumn)) {
                    $attrs[$column] = VendorOtherOption::isOtherTypeId($this->kindKey(), $request->input($this->typeIdColumn))
                        ? VendorOtherOption::normalizeOtherValue($request->input('other_type_value'))
                        : null;
                }
            } else {
                $attrs[$column] = $value;
            }
        }

        return $attrs;
    }

    private function syncVariants(Model $product, $variants, Request $request, bool $replace = true): void
    {
        if (! is_array($variants)) {
            $variants = [];
        }

        $variantModel = $this->variantModel;
        $basePath = public_path($this->imageBasePath((int) $product->user_id));
        $existingRows = $variantModel::where('product_id', $product->id)
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

        foreach ($variants as $row) {
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
            $imageName = $this->resolveVariantImage($request, $product, $index, $row, $previousImage);

            if ($imageName) {
                $keptImageNames[] = $imageName;
            }

            if ($previousImage && $imageName && $previousImage !== $imageName) {
                $oldPath = $basePath.'/'.$previousImage;
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $created = $variantModel::create([
                'product_id' => $product->id,
                'packing_size_id' => isset($row['packingSizeId']) && $row['packingSizeId'] !== ''
                    ? (int) $row['packingSizeId']
                    : null,
                'packing_size' => $row['packingSize'] ?? null,
                'other_size_value' => $row['otherSizeValue'] ?? null,
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
            $variantModel::where('product_id', $product->id)
                ->whereNotIn('id', $createdIds)
                ->delete();

            foreach ($existingRows as $old) {
                if (! $old->image || in_array($old->image, $keptImageNames, true)) {
                    continue;
                }
                $filePath = $basePath.'/'.$old->image;
                if (is_file($filePath)) {
                    @unlink($filePath);
                }
            }
        }
    }

    private function resolveVariantImage(
        Request $request,
        Model $product,
        $index,
        array $row,
        ?string $previousImage = null
    ): ?string {
        $file = $request->file("variants.{$index}.image")
            ?? $request->file("packing_sizes.{$index}.image")
            ?? $request->file("packingSizes.{$index}.image");

        if ($file instanceof UploadedFile && $file->isValid()) {
            return $this->storeVariantImage($product, $file);
        }

        $existing = $row['existingImage'] ?? null;
        if (is_string($existing) && $existing !== '') {
            $filename = basename(parse_url($existing, PHP_URL_PATH) ?: $existing);
            if ($filename !== '' && is_file(public_path($this->imageBasePath((int) $product->user_id).'/'.$filename))) {
                return $filename;
            }
            if ($filename !== '' && ! str_contains($filename, '/')) {
                return $filename;
            }
        }

        if (is_string($previousImage) && $previousImage !== '') {
            return $previousImage;
        }

        return null;
    }

    private function storeVariantImage(Model $product, UploadedFile $file): string
    {
        $dir = public_path($this->imageBasePath((int) $product->user_id));
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $newName = time().'_'.uniqid('', true).'.'.$ext;
        $file->move($dir, $newName);

        return $newName;
    }

    private function serializeProduct(Model $product): array
    {
        $basePath = $this->imageBasePath((int) $product->user_id);
        $typeId = $product->{$this->typeIdColumn};

        $variants = $product->variants->map(function (Model $variant) use ($basePath) {
            return $this->serializeVariantRow($variant, $basePath);
        })->values()->all();

        return [
            'id' => (int) $product->id,
            'userId' => (int) $product->user_id,
            $this->typeIdCamel => $typeId !== null ? (int) $typeId : null,
            'otherTypeValue' => $product->other_type_value,
            'specification' => $product->specification,
            'description' => $product->description,
            'additionalInformation' => $product->additional_information,
            'status' => (int) $product->status,
            'variants' => $variants,
        ];
    }

    private function serializeVariantRow(Model $variant, string $basePath): array
    {
        return [
            'id' => (int) $variant->id,
            'packingSizeId' => $variant->packing_size_id !== null ? (int) $variant->packing_size_id : null,
            'packingSize' => $variant->packing_size,
            'otherSizeValue' => $variant->other_size_value,
            'rate' => $variant->rate !== null ? (string) $variant->rate : null,
            'gst' => $variant->gst !== null ? (string) $variant->gst : null,
            'totalPrice' => $variant->total_price !== null ? (string) $variant->total_price : null,
            'bagSize' => $variant->bag_size,
            'bagWeight' => $variant->bag_weight,
            'image' => $variant->image,
            'imageUrl' => $variant->image ? asset($basePath.'/'.$variant->image) : null,
            'sortOrder' => (int) $variant->sort_order,
        ];
    }
}
