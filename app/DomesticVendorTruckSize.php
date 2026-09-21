<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DomesticVendorTruckSize extends Model
{
    protected $table = 'domestic_vendor_truck_sizes';

    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 0;

    protected $fillable = [
        'size',
        'label',
        'description',
        'status',
    ];

    protected $casts = [
        'size' => 'float',
        'status' => 'integer',
    ];

    public function displayLabel(): string
    {
        if (is_string($this->label) && trim($this->label) !== '') {
            return $this->label;
        }

        $size = rtrim(rtrim(number_format((float) $this->size, 2, '.', ''), '0'), '.');

        return $size.' MT';
    }

    public static function options(?int $includeId = null): array
    {
        return self::query()
            ->where(function ($query) use ($includeId) {
                $query->where('status', self::STATUS_ACTIVE);
                if ($includeId) {
                    $query->orWhere('id', $includeId);
                }
            })
            ->orderBy('size')
            ->get()
            ->mapWithKeys(fn (self $row) => [$row->id => $row->displayLabel()])
            ->toArray();
    }
}
