<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaddyPriceDefault extends Model
{
    protected $table = 'paddy_price_defaults';

    protected $fillable = [
        'default_mandi_id',
        'default_quality_id',
    ];

    /**
     * Single defaults row (id = 1). Creates it when missing.
     */
    public static function current(): self
    {
        $row = self::query()->orderBy('id')->first();
        if ($row) {
            return $row;
        }

        return self::create([
            'default_mandi_id' => null,
            'default_quality_id' => null,
        ]);
    }

    /**
     * Defaults payload for APIs so frontend can pre-select
     * the admin-chosen mandi / crop (quality).
     */
    public function toDefaultsArray(): array
    {
        $mandiId = $this->default_mandi_id ? (int) $this->default_mandi_id : null;
        $qualityId = $this->default_quality_id ? (int) $this->default_quality_id : null;

        return [
            'mandi_id' => $mandiId,
            'mandi' => $mandiId ? PaddyMandiModel::query()->where('id', $mandiId)->value('mandi') : null,
            'quality_id' => $qualityId,
            'quality' => $qualityId ? PaddyQuality::query()->where('id', $qualityId)->value('quality') : null,
        ];
    }
}
