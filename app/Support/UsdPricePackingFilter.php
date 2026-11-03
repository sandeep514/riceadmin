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
    /** @deprecated Not included in historical / export packing filters. */
    public const KEY_40KG_JUTE = '40kg_jute';

    public const KEY_ALL = 'all';

    public const KEY_JUTE_INNER = 'jute_inner';

    public const KEY_50KG_PP = '50kg_pp';

    /**
     * Historical USD data and export packing filters: 50kg PP and 40kg Jute Inner only.
     *
     * @return array<int, array{key:string, label:string, typeLabel:string}>
     */
    public static function packingDefinitions(): array
    {
        return [
            [
                'key' => self::KEY_50KG_PP,
                'typeLabel' => 'PP',
                'label' => '50kg PP',
            ],
            [
                'key' => self::KEY_JUTE_INNER,
                'typeLabel' => 'Jute Inner',
                'label' => '40kg Jute Inner',
            ],
        ];
    }

    public static function displayLabel(string $packingKey, ?string $bagSize): string
    {
        $typeLabel = '';
        foreach (self::packingDefinitions() as $def) {
            if ($def['key'] === $packingKey) {
                $typeLabel = $def['typeLabel'];
                break;
            }
        }

        $size = trim((string) $bagSize);
        if ($size === '') {
            return $typeLabel !== '' ? $typeLabel : $packingKey;
        }
        if ($typeLabel === '') {
            return $size;
        }

        return $size.' '.$typeLabel;
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
        if (self::isAllKey($packingKey)) {
            return 'All';
        }

        foreach (self::packingDefinitions() as $def) {
            if ($def['key'] === $packingKey) {
                return $def['label'];
            }
        }

        return (string) $packingKey;
    }

    public static function isAllKey(?string $packingKey): bool
    {
        if ($packingKey === null) {
            return true;
        }

        $key = strtolower(trim($packingKey));

        return $key === '' || $key === self::KEY_ALL;
    }

    /**
     * packingKey values accepted by POST /api/usd-prices/export.
     * `all` is the UI "All packings" option and maps to the two allowed styles.
     *
     * @return list<string>
     */
    public static function exportKeys(): array
    {
        return array_merge([self::KEY_ALL], self::allowedKeys());
    }

    /**
     * Apply bag_size / bag_type match for one packing key onto a query.
     */
    public static function applyKeyMatch(Builder $query, string $packingKey): Builder
    {
        return match ($packingKey) {
            self::KEY_JUTE_INNER => $query
                ->whereRaw('LOWER(TRIM(bag_size)) = ?', ['40kg'])
                ->where(function ($inner) {
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
     * Resolve packingKey to USD_defaultmaster ids.
     *
     * Rice type is not applied here. Calculator saves historically store basmati
     * prices against applied_for=0 packing rows and non-basmati against
     * applied_for=1 (inverted vs the packing master labels). Scoping ids by type
     * therefore returned packing ids that never appear on matching USD_prices
     * rows, so type=basmati / type=non-basmati exports were empty while type=All
     * (both applied_for sets) worked.
     *
     * Type is filtered on quality_master in CalculatorExport instead.
     *
     * - packing All / all / omit → only 50kg PP + 40kg Jute Inner (both applied_for)
     * - packing 50kg_pp → both applied_for ids for that style
     *
     * @return list<int>
     */
    public static function resolveMasterIds(?string $type, ?string $packingKey): array
    {
        $keys = self::isAllKey($packingKey)
            ? self::allowedKeys()
            : [strtolower(trim((string) $packingKey))];

        $ids = [];
        foreach ($keys as $key) {
            if (! in_array($key, self::allowedKeys(), true)) {
                continue;
            }

            $rows = USD_defaultmaster::query()
                ->where(function ($q) use ($key) {
                    self::applyKeyMatch($q, $key);
                })
                ->orderBy('order', 'ASC')
                ->orderBy('id', 'ASC')
                ->get(['id']);

            foreach ($rows as $row) {
                $ids[] = (int) $row->id;
            }
        }

        return array_values(array_unique($ids));
    }
}
