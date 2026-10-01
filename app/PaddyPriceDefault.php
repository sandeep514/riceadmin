<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaddyPriceDefault extends Model
{
    protected $table = 'paddy_price_defaults';

    protected $fillable = [
        'default_view_by',
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
            'default_view_by' => 'mandi',
        ]);
    }

    /**
     * Defaults payload for APIs so frontend knows which
     * "View by" button (Mandis / Crops) is active by default.
     */
    public function toDefaultsArray(): array
    {
        $viewBy = strtolower(trim((string) $this->default_view_by));

        return [
            'view_by' => $viewBy === 'crop' ? 'crop' : 'mandi',
        ];
    }
}
