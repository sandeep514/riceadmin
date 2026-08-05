<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaddyQuality extends Model
{
    protected $table = 'paddy_qualities';

    protected $fillable = [
        'type',
        'quality',
        'description',
        'order',
        'status',
    ];

    /**
     * Same options as Rice Name master (basmati / non-basmati).
     */
    public static function riceTypeOptions(): array
    {
        return [
            'basmati' => 'Basmati',
            'non-basmati' => 'Non Basmati',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::riceTypeOptions()[$this->type] ?? ($this->type ?: '-');
    }
}


