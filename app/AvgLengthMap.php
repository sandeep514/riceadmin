<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AvgLengthMap extends Model
{
    protected $table = 'avg_length_maps';

    protected $fillable = [
        'quality_type',
        'rice_name_id',
        'form_id',
        'wand_id',
        'avg_length',
    ];

    protected $casts = [
        'avg_length' => 'decimal:2',
    ];

    public function riceName()
    {
        return $this->belongsTo(RiceName::class, 'rice_name_id');
    }

    public function form()
    {
        return $this->belongsTo(RiceFormMilestone3::class, 'form_id');
    }

    public function wand()
    {
        return $this->belongsTo(WandModel::class, 'wand_id');
    }

    public function getGradeLabelAttribute(): string
    {
        $wand = $this->wand;
        if (! $wand) {
            return '-';
        }
        $wand->loadMissing('getWandType');

        return $wand->getWandType
            ? $wand->getWandType->type.' - '.$wand->value
            : (string) $wand->value;
    }
}
