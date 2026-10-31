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
     * @return array<int, array{key:string, label:string, typeLabel:string}>
     */
    public static function packingDefinitions(): array
    {
        return [
            [
                'key' => self::KEY_40KG_JUTE,
                'typeLabel' => 'Jute',
                'label' => '40kg Jute',
            ],
            [
                'key' => self::KEY_JUTE_INNER,
                'typeLabel' => 'Jute Inner',
                'label' => '40kg Jute Inner',
            ],
            [
                'key' => self::KEY_50KG_PP,
                'typeLabel' => 'PP',
                'label' => '50kg PP',
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
     * - packing All → up to 6 ids (3 styles × both applied_for)
     * - packing 50kg_pp → both applied_for ids for that style
     *
     * @return list<int>
     */
    public static function resolveMasterIds(?string $type, ?string $packingKey): array
    {
        $keys = ($packingKey === null || $packingKey === '')
            ? self::allowedKeys()
            : [strtolower(trim($packingKey))];

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
