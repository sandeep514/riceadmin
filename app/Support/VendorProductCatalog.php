<?php

namespace App\Support;

use App\Category;
use App\WebBusinessDetails;
use App\WebCartoonProduct;
use App\WebCylinderProduct;
use App\WebLabEquipmentProduct;
use App\WebMachineryEquipmentProduct;
use App\WebRiceBagProduct;
use Illuminate\Database\Eloquent\Model;

final class VendorProductCatalog
{
    public const KIND_RICE_BAG = 'rice_bag';

    public const KIND_CARTOON = 'cartoon';

    public const KIND_CYLINDER = 'cylinder';

    public const KIND_LAB_EQUIPMENT = 'lab_equipment';

    public const KIND_MACHINERY_EQUIPMENT = 'machinery_equipment';

    /**
     * @return array<string, class-string<Model>>
     */
    public static function productModels(): array
    {
        return [
            self::KIND_RICE_BAG => WebRiceBagProduct::class,
            self::KIND_CARTOON => WebCartoonProduct::class,
            self::KIND_CYLINDER => WebCylinderProduct::class,
            self::KIND_LAB_EQUIPMENT => WebLabEquipmentProduct::class,
            self::KIND_MACHINERY_EQUIPMENT => WebMachineryEquipmentProduct::class,
        ];
    }

    public static function detectKindFromCategoryId(?int $categoryId): ?string
    {
        if ($categoryId === null || $categoryId <= 0) {
            return null;
        }

        $name = strtolower((string) (Category::query()->where('id', $categoryId)->value('category') ?? ''));

        return self::detectKindFromCategoryName($name);
    }

    public static function detectKindFromCategoryName(?string $name): ?string
    {
        $name = strtolower(trim((string) $name));
        if ($name === '') {
            return null;
        }

        if (str_contains($name, 'lab')) {
            return self::KIND_LAB_EQUIPMENT;
        }

        if (str_contains($name, 'machinery') || str_contains($name, 'machine')) {
            return self::KIND_MACHINERY_EQUIPMENT;
        }

        if (str_contains($name, 'cartoon') || str_contains($name, 'carton')) {
            return self::KIND_CARTOON;
        }

        if (str_contains($name, 'cylinder')) {
            return self::KIND_CYLINDER;
        }

        if (str_contains($name, 'rice bag') || (str_contains($name, 'bag') && ! str_contains($name, 'cartoon'))) {
            return self::KIND_RICE_BAG;
        }

        return null;
    }

    public static function detectKindForVendor(WebBusinessDetails $vendor): ?string
    {
        $raw = $vendor->selected_category ?? null;
        $fromId = self::detectKindFromCategoryId((int) $raw);
        if ($fromId !== null) {
            return $fromId;
        }

        return self::detectKindFromCategoryName(is_string($raw) ? $raw : null);
    }

    /**
     * @param  array<int>  $ownerIds
     */
    public static function detectKindFromOwnerProducts(array $ownerIds): ?string
    {
        $ownerIds = array_values(array_unique(array_filter(array_map('intval', $ownerIds))));
        if ($ownerIds === []) {
            return null;
        }

        foreach (self::productModels() as $kind => $model) {
            if ($model::query()->whereIn('user_id', $ownerIds)->exists()) {
                return $kind;
            }
        }

        return null;
    }

    /**
     * All product owner ids for a vendor kind (cartoon, cylinder, rice_bag, ...).
     * If kind is unknown, checks every catalog table.
     *
     * @return array<int, true>
     */
    public static function productOwnerIdsForKind(?string $kind): array
    {
        $models = self::productModels();
        $toQuery = ($kind !== null && isset($models[$kind]))
            ? [$models[$kind]]
            : array_values($models);

        $found = [];
        foreach ($toQuery as $model) {
            foreach ($model::query()->distinct()->pluck('user_id') as $id) {
                $id = (int) $id;
                if ($id > 0) {
                    $found[$id] = true;
                }
            }
        }

        return $found;
    }

    /**
     * Owner ids that have at least one catalog product.
     * Checks rice bag, cartoon, cylinder, lab equipment and machinery equipment tables.
     * Matches products stored under users.id or web_business_details.id.
     *
     * @param  array<int>  $ownerIds
     * @return array<int, true>
     */
    public static function userIdsWithVerifiedProducts(array $ownerIds): array
    {
        return self::ownerIdsWithProducts($ownerIds, verifiedOnly: false);
    }

    /**
     * @param  array<int>  $ownerIds
     * @return array<int, true>
     */
    public static function ownerIdsWithProducts(array $ownerIds, bool $verifiedOnly = false): array
    {
        $ownerIds = array_values(array_unique(array_filter(array_map('intval', $ownerIds))));
        if ($ownerIds === []) {
            return [];
        }

        $found = [];

        foreach (self::productModels() as $model) {
            $query = $model::query()->whereIn('user_id', $ownerIds);
            if ($verifiedOnly) {
                $query->where('status', 1);
            }

            foreach ($query->distinct()->pluck('user_id') as $id) {
                $found[(int) $id] = true;
            }
        }

        return $found;
    }
}
