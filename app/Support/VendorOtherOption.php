<?php

namespace App\Support;

use App\BagSize;
use App\CartonSize;
use App\CartoonType;
use App\CylinderSize;
use App\CylinderType;
use App\PackingType;
use App\PublicPacking;
use App\VendorPackingType;

class VendorOtherOption
{
    public static function isOtherLabel($label): bool
    {
        if (! is_string($label)) {
            return false;
        }

        return strcasecmp(trim($label), 'Other') === 0;
    }

    public static function isOtherTypeId(string $kind, $typeId): bool
    {
        if ($typeId === null || $typeId === '') {
            return false;
        }

        $id = (int) $typeId;
        if ($id <= 0) {
            return false;
        }

        if ($kind === 'rice_bag' || $kind === 'bag') {
            $vendorName = VendorPackingType::query()->where('id', $id)->value('name');
            if ($vendorName !== null) {
                return self::isOtherLabel($vendorName);
            }

            $packingName = PackingType::query()->where('id', $id)->value('name');

            return self::isOtherLabel($packingName);
        }

        if ($kind === 'cartoon' || $kind === 'carton') {
            return self::isOtherLabel(CartoonType::query()->where('id', $id)->value('type'));
        }

        if ($kind === 'cylinder') {
            return self::isOtherLabel(CylinderType::query()->where('id', $id)->value('type'));
        }

        return false;
    }

    public static function isOtherSizeId(string $kind, $sizeId): bool
    {
        if ($sizeId === null || $sizeId === '') {
            return false;
        }

        $id = (int) $sizeId;
        if ($id <= 0) {
            return false;
        }

        $sizeLabel = null;

        if ($kind === 'rice_bag' || $kind === 'bag') {
            $sizeLabel = BagSize::query()->where('id', $id)->value('size');
        } elseif ($kind === 'cartoon' || $kind === 'carton') {
            $sizeLabel = CartonSize::query()->where('id', $id)->value('size');
        } elseif ($kind === 'cylinder') {
            $sizeLabel = CylinderSize::query()->where('id', $id)->value('size');
        }

        if ($sizeLabel !== null) {
            return self::isOtherLabel($sizeLabel);
        }

        return self::isOtherLabel(PublicPacking::query()->where('id', $id)->value('size'));
    }

    public static function normalizeOtherValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * Resolve otherSizeValue for a variant row.
     * Frontend may send the custom Other text as packingSize / packing_size
     * instead of otherSizeValue / other_size_value.
     *
     * @param  array<string, mixed>  $row
     * @return array{0: ?string, 1: ?string} [packingSizeLabel, otherSizeValue]
     */
    public static function resolvePackingSizeOther(string $kind, $sizeId, array $row): array
    {
        $incomingPackingSize = $row['packingSize'] ?? $row['packing_size'] ?? null;

        if (! self::isOtherSizeId($kind, $sizeId)) {
            return [$incomingPackingSize, null];
        }

        $otherSizeValue = self::normalizeOtherValue(
            $row['otherSizeValue'] ?? $row['other_size_value'] ?? null
        );

        if ($otherSizeValue === null) {
            $fallback = self::normalizeOtherValue($incomingPackingSize);
            if ($fallback !== null && ! self::isOtherLabel($fallback)) {
                $otherSizeValue = $fallback;
            }
        }

        // Keep master label in packing_size; custom text lives in other_size_value.
        return ['Other', $otherSizeValue];
    }
}
