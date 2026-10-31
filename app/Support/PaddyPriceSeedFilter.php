<?php

namespace App\Support;

use App\PaddyQuality;
use Illuminate\Database\Eloquent\Builder;

/**
 * Resolves paddy export type + seed filters to paddy_qualities ids.
 *
 * Seeds (1121, 1509, …) live in paddy_qualities.quality and are scoped by
 * type (basmati / non-basmati). The same seed name can exist on both types.
 */
class PaddyPriceSeedFilter
{
    /**
     * Match paddy_qualities.type to basmati | non-basmati.
     * Accepts stored values such as "basmati", "Non Basmati", "non-basmati".
     */
    public static function applyTypeMatch(Builder $query, string $type): Builder
    {
        $normalized = self::normalizedType($type);

        if ($normalized === 'basmati') {
            return $query->whereRaw(self::typeNormalizeSql().' = ?', ['basmati']);
        }

        if ($normalized === 'nonbasmati') {
            return $query->whereRaw(self::typeNormalizeSql().' = ?', ['nonbasmati']);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function normalizedType(?string $type): string
    {
        $type = is_string($type) ? strtolower(trim($type)) : '';

        return str_replace([' ', '-'], '', $type);
    }

    public static function canonicalType(?string $type): ?string
    {
        $normalized = self::normalizedType($type);
        if ($normalized === 'basmati') {
            return 'basmati';
        }
        if ($normalized === 'nonbasmati') {
            return 'non-basmati';
        }

        return null;
    }

    /**
     * @return list<array{id:int, key:string, label:string, type:string, type_label:string}>
     */
    public static function listSeeds(?string $type = null): array
    {
        $query = PaddyQuality::query()
            ->select(['id', 'type', 'quality'])
            ->where('status', 1)
            ->orderByRaw('`order` IS NULL, `order` ASC')
            ->orderBy('id');

        if (self::canonicalType($type) !== null) {
            self::applyTypeMatch($query, (string) $type);
        }

        return $query->get()->map(function ($row) {
            $quality = trim((string) $row->quality);
            $canonical = self::canonicalType($row->type) ?? (string) $row->type;

            return [
                'id' => (int) $row->id,
                'key' => $quality,
                'label' => $quality !== '' ? $quality : (string) $row->id,
                'type' => $canonical,
                'type_label' => $row->type_label,
            ];
        })->values()->all();
    }

    /**
     * Quality ids for the selected type + seed.
     *
     * - type All + seed All → null (no quality filter)
     * - type basmati + seed All → all basmati quality ids
     * - type All + seedKey 1121 → every 1121 id (both types)
     * - type basmati + seedKey 1121 → basmati 1121 ids only
     *
     * @return list<int>|null null = no quality filter; [] = force empty result
     */
    public static function resolveQualityIds(?string $type, ?string $seedKey, ?int $seedId = null): ?array
    {
        $canonicalType = self::canonicalType($type);
        $seedKey = is_string($seedKey) ? trim($seedKey) : '';
        $hasKey = $seedKey !== '';
        $hasId = $seedId !== null && $seedId > 0;

        if ($canonicalType === null && ! $hasKey && ! $hasId) {
            return null;
        }

        $query = PaddyQuality::query()->select(['id']);

        if ($canonicalType !== null) {
            self::applyTypeMatch($query, $canonicalType);
        }

        if ($hasKey) {
            $query->whereRaw('LOWER(TRIM(quality)) = ?', [strtolower($seedKey)]);
        } elseif ($hasId) {
            $query->where('id', $seedId);
        }

        return $query->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private static function typeNormalizeSql(): string
    {
        return "LOWER(REPLACE(REPLACE(TRIM(type), ' ', ''), '-', ''))";
    }
}
