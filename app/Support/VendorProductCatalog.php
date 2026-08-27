<?php

namespace App\Support;

use App\Category;
use App\WebBusinessDetails;
use App\WebCartoonProduct;
use App\WebCylinderProduct;
use App\WebRiceBagProduct;
use Illuminate\Database\Eloquent\Model;

final class VendorProductCatalog
{
    public const KIND_RICE_BAG = 'rice_bag';

    public const KIND_CARTOON = 'cartoon';

    public const KIND_CYLINDER = 'cylinder';

    /**
     * @return array<string, class-string<Model>>
     */
    public static function productModels(): array
    {
        return [
            self::KIND_RICE_BAG => WebRiceBagProduct::class,
            self::KIND_CARTOON => WebCartoonProduct::class,
            self::KIND_CYLINDER => WebCylinderProduct::class,
        ];
    }

    public static function detectKindFromCategoryId(?int $categoryId): ?string
    {
        if ($categoryId === null || $categoryId <= 0) {
            return null;
        }

        $name = strtolower((string) (Category::query()->where('id', $categoryId)->value('category') ?? ''));
        if ($name === '') {
            return null;
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
        return self::detectKindFromCategoryId((int) ($vendor->selected_category ?? 0));
    }

    /**
     * @param  array<int>  $userIds
     * @return array<int, true>
     */
    public static function userIdsWithVerifiedProducts(array $userIds): array
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if ($userIds === []) {
            return [];
        }

        $found = [];

        $riceBagUserIds = WebRiceBagProduct::query()
            ->whereIn('user_id', $userIds)
            ->where('status', 1)
            ->whereHas('packingSizes')
            ->distinct()
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();
        foreach ($riceBagUserIds as $id) {
            $found[$id] = true;
        }

        $cartoonUserIds = WebCartoonProduct::query()
            ->whereIn('user_id', $userIds)
            ->where('status', 1)
            ->whereHas('variants')
            ->distinct()
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();
        foreach ($cartoonUserIds as $id) {
            $found[$id] = true;
        }

        $cylinderUserIds = WebCylinderProduct::query()
            ->whereIn('user_id', $userIds)
            ->where('status', 1)
            ->whereHas('variants')
            ->distinct()
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();
        foreach ($cylinderUserIds as $id) {
            $found[$id] = true;
        }

        return $found;
    }
}
