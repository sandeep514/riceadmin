<?php

namespace App\Services;

use App\LabEquipment;
use App\MachineryEquipment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Vendor products where each variant is one equipment row:
 * equipment + rate + description + catalogue image.
 */
class WebVendorEquipmentProductService
{
    /** @var class-string<Model> */
    private string $productModel;

    /** @var class-string<Model> */
    private string $variantModel;

    /** @var class-string<Model> */
    private string $equipmentModel;

    private string $productsTable;

    private string $equipmentTable;

    private string $label;

    private string $uploadFolder;

    public static function lab(): self
    {
        return new self(
            productModel: \App\WebLabEquipmentProduct::class,
            variantModel: \App\WebLabEquipmentProductVariant::class,
            equipmentModel: LabEquipment::class,
            productsTable: 'web_lab_equipment_products',
            equipmentTable: 'lab_equipments',
            label: 'Lab equipment',
            uploadFolder: 'lab-equipment-products',
        );
    }

    public static function machinery(): self
    {
        return new self(
            productModel: \App\WebMachineryEquipmentProduct::class,
            variantModel: \App\WebMachineryEquipmentProductVariant::class,
            equipmentModel: MachineryEquipment::class,
            productsTable: 'web_machinery_equipment_products',
            equipmentTable: 'machinery_equipments',
            label: 'Machinery equipment',
            uploadFolder: 'machinery-equipment-products',
        );
    }

    /**
     * @param  class-string<Model>  $productModel
     * @param  class-string<Model>  $variantModel
     * @param  class-string<Model>  $equipmentModel
     */
    private function __construct(
        string $productModel,
        string $variantModel,
        string $equipmentModel,
        string $productsTable,
        string $equipmentTable,
        string $label,
        string $uploadFolder
    ) {
        $this->productModel = $productModel;
        $this->variantModel = $variantModel;
        $this->equipmentModel = $equipmentModel;
        $this->productsTable = $productsTable;
        $this->equipmentTable = $equipmentTable;
        $this->label = $label;
        $this->uploadFolder = $uploadFolder;
    }

    private function kindKey(): string
    {
        return $this->equipmentTable === 'machinery_equipments' ? 'machinery_equipment' : 'lab_equipment';
    }

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
                'received_keys' => array_values(array_keys($request->except(['variants', 'products']))),
            ], 422);
        }

        $productModel = $this->productModel;

        $product = DB::transaction(function () use ($request, $productModel) {
            /** @var Model $product */
            $product = $productModel::create([
                'user_id' => (int) $request->input('user_id'),
                'status' => 0,
            ]);

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
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
                'received_keys' => array_values(array_keys($request->except(['variants', 'products']))),
            ], 422);
        }

        $product = $this->productModel::with(['variants'])->find((int) $request->input('id'));
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
                'equipment_id' => $row->equipment_id,
            ];
        });

        $product = DB::transaction(function () use ($request, $product) {
            if ($request->exists('variants')) {
                $this->syncVariants($product, $request->input('variants', []), $request, replace: true);
            }

            return $product->load(['variants']);
        });

        $action = ($request->exists('variants')
            && VendorProductAdminNotificationService::hasNewEquipmentVariants($previousVariants, $product->variants))
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
            if ($variant->catalogue) {
                $filePath = $basePath.'/'.$variant->catalogue;
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

    /** Remove catalogue file for one variant row. */
    public function deleteCatalogue(Request $request, $variantId)
    {
        $variant = $this->variantModel::find((int) $variantId);
        if ($variant === null) {
            return response()->json([
                'status' => false,
                'message' => 'Variant not found.',
            ], 404);
        }

        $product = $this->productModel::find($variant->product_id);
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => $this->label.' product not found.',
            ], 404);
        }

        if ($denied = $this->denyIfNotOwner($request, (int) $product->user_id, 'delete this catalogue')) {
            return $denied;
        }

        if ($variant->catalogue) {
            $filePath = public_path($this->imageBasePath((int) $product->user_id).'/'.$variant->catalogue);
            if (is_file($filePath)) {
                @unlink($filePath);
            }
            $variant->update(['catalogue' => null]);
        }

        $product->load(['variants']);

        return response()->json([
            'status' => true,
            'message' => 'Catalogue deleted successfully.',
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
        $ownerIds = array_values(array_unique(array_filter(array_map('intval', $ownerIds))));
        if ($ownerIds === []) {
            return $this->productModel::query()->whereRaw('1 = 0')->get();
        }

        return $this->productModel::with(['variants'])
            ->whereIn('user_id', $ownerIds)
            ->where('status', 1)
            ->whereHas('variants')
            ->orderByDesc('id')
            ->get();
    }

    public function serializeVendorProduct(Model $product): array
    {
        $basePath = $this->imageBasePath((int) $product->user_id);

        return [
            'id' => (int) $product->id,
            'variants' => $product->variants
                ->map(fn (Model $variant) => $this->serializeVariantRow($variant, $basePath))
                ->values()
                ->all(),
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

    /**
     * @return array{ok:bool, activated?:bool, deactivated?:bool}|false
     */
    public function toggleStatus(int $id, ?string $reason = null)
    {
        $product = $this->productModel::find($id);
        if ($product === null) {
            return false;
        }

        $wasActive = (int) $product->status === 1;
        if ($wasActive) {
            $reason = is_string($reason) ? trim($reason) : '';
            if ($reason === '') {
                return ['ok' => false, 'deactivated' => false, 'missing_reason' => true];
            }

            $product->update(['status' => 0]);
            VendorProductAdminNotificationService::notifyDeactivated(
                $this->kindKey(),
                $product->fresh(),
                $reason
            );

            return ['ok' => true, 'deactivated' => true];
        }

        $product->update(['status' => 1]);
        VendorProductAdminNotificationService::notifyAccepted($this->kindKey(), $product->fresh());

        return ['ok' => true, 'activated' => true];
    }

    public function equipmentOptions(): array
    {
        return $this->equipmentModel::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    private function createRules(): array
    {
        return array_merge([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'variants' => ['required', 'array', 'min:1'],
        ], $this->variantRules());
    }

    private function updateRules(): array
    {
        return array_merge([
            'id' => ['required', 'integer', 'exists:'.$this->productsTable.',id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'variants' => ['sometimes', 'array', 'min:1'],
        ], $this->variantRules());
    }

    private function variantRules(): array
    {
        return [
            'variants.*.equipmentId' => ['required', 'integer', 'exists:'.$this->equipmentTable.',id'],
            'variants.*.rate' => ['required', 'numeric'],
            'variants.*.description' => ['nullable', 'string'],
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

        if (! $request->exists('variants') && ! $request->filled('user_id')) {
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

        foreach (['products', 'equipments', 'items'] as $from) {
            if ($request->exists($from) && ! $request->exists('variants')) {
                $request->merge(['variants' => $request->input($from)]);
            }
        }

        if ($request->filled('userId') && ! $request->filled('user_id')) {
            $request->merge(['user_id' => $request->input('userId')]);
        }

        if ($request->has('id') && ($request->input('id') === '' || $request->input('id') === null)) {
            $request->request->remove('id');
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

                $existingCatalogue = $row['existingCatalogue']
                    ?? $row['existing_catalogue']
                    ?? $row['catalogue']
                    ?? $row['catalogue_name']
                    ?? null;
                if (is_string($existingCatalogue) && $existingCatalogue !== '') {
                    $existingCatalogue = basename(parse_url($existingCatalogue, PHP_URL_PATH) ?: $existingCatalogue);
                    if ($existingCatalogue === '' || str_contains($existingCatalogue, ' ')) {
                        $existingCatalogue = null;
                    }
                } else {
                    $existingCatalogue = null;
                }

                $normalizedRows[] = [
                    'id' => $row['id'] ?? $row['variant_row_id'] ?? null,
                    'equipmentId' => $row['equipmentId']
                        ?? $row['equipment_id']
                        ?? $row['labEquipmentId']
                        ?? $row['lab_equipment_id']
                        ?? $row['machineryEquipmentId']
                        ?? $row['machinery_equipment_id']
                        ?? null,
                    'rate' => $row['rate'] ?? null,
                    'description' => $row['description'] ?? null,
                    'existingCatalogue' => $existingCatalogue,
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
        if ($authUser && $ownerUserId !== (int) $authUser->id) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden: You are not allowed to '.$action.' this product.',
            ], 403);
        }

        return null;
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
        $existingByEquipmentId = $existingRows
            ->filter(fn ($row) => $row->equipment_id !== null)
            ->keyBy(fn ($row) => (string) $row->equipment_id);
        $existingByIndex = $existingRows->values();

        $equipmentNames = $this->equipmentModel::query()->pluck('name', 'id');

        $keptFiles = [];
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
            } elseif (! empty($row['equipmentId']) && $existingByEquipmentId->has((string) $row['equipmentId'])) {
                $matched = $existingByEquipmentId->get((string) $row['equipmentId']);
            } elseif ($existingByIndex->has($index)) {
                $matched = $existingByIndex->get($index);
            }

            $previousFile = $matched?->catalogue;
            $catalogue = $this->resolveCatalogueFile($request, $product, $index, $row, $previousFile);

            if ($catalogue) {
                $keptFiles[] = $catalogue;
            }

            if ($previousFile && $catalogue && $previousFile !== $catalogue) {
                $oldPath = $basePath.'/'.$previousFile;
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $equipmentId = isset($row['equipmentId']) && $row['equipmentId'] !== ''
                ? (int) $row['equipmentId']
                : null;

            $created = $variantModel::create([
                'product_id' => $product->id,
                'equipment_id' => $equipmentId,
                'equipment_name' => $equipmentId !== null ? ($equipmentNames[$equipmentId] ?? null) : null,
                'rate' => $row['rate'] ?? null,
                'description' => $row['description'] ?? null,
                'catalogue' => $catalogue,
                'sort_order' => $sortOrder,
            ]);
            $createdIds[] = $created->id;
        }

        if ($replace) {
            $variantModel::where('product_id', $product->id)
                ->whereNotIn('id', $createdIds)
                ->delete();

            foreach ($existingRows as $old) {
                if (! $old->catalogue || in_array($old->catalogue, $keptFiles, true)) {
                    continue;
                }
                $filePath = $basePath.'/'.$old->catalogue;
                if (is_file($filePath)) {
                    @unlink($filePath);
                }
            }
        }
    }

    private function resolveCatalogueFile(
        Request $request,
        Model $product,
        $index,
        array $row,
        ?string $previousFile = null
    ): ?string {
        $file = $request->file("variants.{$index}.catalogue")
            ?? $request->file("variants.{$index}.image")
            ?? $request->file("products.{$index}.catalogue");

        if ($file instanceof UploadedFile && $file->isValid()) {
            return $this->storeCatalogueFile($product, $file);
        }

        $existing = $row['existingCatalogue'] ?? null;
        if (is_string($existing) && $existing !== '') {
            $filename = basename(parse_url($existing, PHP_URL_PATH) ?: $existing);
            if ($filename !== '' && ! str_contains($filename, '/')) {
                return $filename;
            }
        }

        if (is_string($previousFile) && $previousFile !== '') {
            return $previousFile;
        }

        return null;
    }

    private function storeCatalogueFile(Model $product, UploadedFile $file): string
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

        return [
            'id' => (int) $product->id,
            'userId' => (int) $product->user_id,
            'status' => (int) $product->status,
            'variants' => $product->variants
                ->map(fn (Model $variant) => $this->serializeVariantRow($variant, $basePath))
                ->values()
                ->all(),
        ];
    }

    private function serializeVariantRow(Model $variant, string $basePath): array
    {
        return [
            'id' => (int) $variant->id,
            'equipmentId' => $variant->equipment_id !== null ? (int) $variant->equipment_id : null,
            'equipmentName' => $variant->equipment_name,
            'rate' => $variant->rate !== null ? (string) $variant->rate : null,
            'description' => $variant->description,
            'catalogue' => $variant->catalogue,
            'catalogueUrl' => $variant->catalogue ? asset($basePath.'/'.$variant->catalogue) : null,
            'sortOrder' => (int) $variant->sort_order,
        ];
    }
}
