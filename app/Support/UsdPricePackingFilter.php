<?php

namespace App\Support;

use App\USD_defaultmaster;
use Illuminate\Database\Eloquent\Builder;

/**
 * Resolves USD export packing UI keys to USD_defaultmaster ids.
 *
 * Packing styles are shared labels; basmati (applied_for=1) and non-basmati
 * (applied_for=0) each have their own master row ids.
 */
class UsdPricePackingFilter
{
    public const KEY_40KG_JUTE = '40kg_jute';

    public const KEY_JUTE_INNER = 'jute_inner';

    public const KEY_50KG_PP = '50kg_pp';

    /**
     * @return array<int, array{key:string, label:string}>
     */
    public static function packingDefinitions(): array
    {
        return [
            [
                'key' => self::KEY_40KG_JUTE,
                'label' => '40kg Jute',
            ],
            [
                'key' => self::KEY_JUTE_INNER,
                'label' => 'Jute Inner',
            ],
            [
                'key' => self::KEY_50KG_PP,
                'label' => '50kg PP',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function allowedKeys(): array
    {
        return array_column(self::packingDefinitions(), 'key');
    }

    public static function labelForKey(?string $packingKey): string
    {
        if ($packingKey === null || $packingKey === '') {
            return 'All';
        }

        foreach (self::packingDefinitions() as $def) {
            if ($def['key'] === $packingKey) {
                return $def['label'];
            }
        }

        return $packingKey;
    }

    /**
     * Apply bag_size / bag_type match for one packing key onto a query.
     */
    public static function applyKeyMatch(Builder $query, string $packingKey): Builder
    {
        return match ($packingKey) {
            self::KEY_40KG_JUTE => $query
                ->whereRaw('LOWER(TRIM(bag_size)) = ?', ['40kg'])
                ->whereRaw('LOWER(TRIM(bag_type)) = ?', ['jute']),
            self::KEY_JUTE_INNER => $query->where(function ($inner) {
                $inner->whereRaw('LOWER(TRIM(bag_type)) = ?', ['jute inner'])
                    ->orWhereRaw('LOWER(REPLACE(TRIM(bag_type), \'-\', \' \')) LIKE ?', ['%jute%inner%']);
            }),
            self::KEY_50KG_PP => $query
                ->whereRaw('LOWER(TRIM(bag_size)) = ?', ['50kg'])
                ->whereRaw('LOWER(TRIM(bag_type)) = ?', ['pp']),
            default => $query->whereRaw('1 = 0'),
        };
    }

    /**
     * @return list<int> applied_for values (1=basmati, 0=non-basmati)
     */
    public static function appliedForList(?string $type): array
    {
        $type = is_string($type) ? strtolower(trim($type)) : '';

        if ($type === 'basmati') {
            return [1];
        }
        if ($type === 'non-basmati') {
            return [0];
        }

        // All
        return [0, 1];
    }

    /**
     * Resolve type + packingKey to master ids.
     *
     * - type All + packing All → up to 6 ids
     * - type All + packing 50kg_pp → 2 ids
     * - type basmati + packing 50kg_pp → 1 id
     *
     * @return list<int>|null null means no packing filter (All packings for the type scope
     *                        is still constrained via quality type on export; when packing
     *                        is All we still limit to the 3 known styles' ids so only
     *                        filter packings are exported)
     */
    public static function resolveMasterIds(?string $type, ?string $packingKey): array
    {
        $appliedForList = self::appliedForList($type);
        $keys = ($packingKey === null || $packingKey === '')
            ? self::allowedKeys()
            : [strtolower(trim($packingKey))];

        $ids = [];
        foreach ($appliedForList as $appliedFor) {
            foreach ($keys as $key) {
                if (! in_array($key, self::allowedKeys(), true)) {
                    continue;
                }

                $row = USD_defaultmaster::query()
                    ->where('applied_for', $appliedFor)
                    ->where(function ($q) use ($key) {
                        self::applyKeyMatch($q, $key);
                    })
                    ->orderBy('order', 'ASC')
                    ->orderBy('id', 'ASC')
                    ->first(['id']);

                if ($row) {
                    $ids[] = (int) $row->id;
                }
            }
        }

        return array_values(array_unique($ids));
    }
}
