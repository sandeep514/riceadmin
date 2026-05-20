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
        'wand_ids',
    ];

    protected $casts = [
        'wand_ids' => 'array',
    ];

    public function riceName()
    {
        return $this->belongsTo(RiceName::class, 'rice_name_id');
    }

    public function form()
    {
        return $this->belongsTo(RiceFormMilestone3::class, 'form_id');
    }

    public function getGradeNamesAttribute(): string
    {
        if (! $this->wand_ids || $this->wand_ids === []) {
            return '-';
        }

        return WandModel::with('getWandType')
            ->whereIn('id', $this->wand_ids)
            ->orderBy('order')
            ->get()
            ->map(function ($wand) {
                return $wand->getWandType
                    ? $wand->getWandType->type.' - '.$wand->value
                    : $wand->value;
            })
            ->implode(', ');
    }
}
