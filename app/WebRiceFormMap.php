<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\WandModel;

class WebRiceFormMap extends Model
{
    protected $table = 'web_rice_form_map';

    protected $fillable = ['rice_type', 'rice_name_id', 'group_name', 'form_ids', 'wand_ids'];

    protected $casts = [
        'wand_ids' => 'array',
    ];

    public function riceName()
    {
        return $this->belongsTo(RiceName::class, 'rice_name_id');
    }

    public function getFormNamesAttribute()
    {
        if (!$this->form_ids) {
            return '';
        }
        $form = RiceFormMilestone3::find($this->form_ids);
        return $form ? $form->name : '';
    }

    public function getWandNamesAttribute()
    {
        if (!$this->wand_ids) {
            return '';
        }
        $wands = WandModel::with('getWandType')
            ->whereIn('id', $this->wand_ids)
            ->orderBy('order')
            ->get()
            ->map(function ($wand) {
                return $wand->getWandType ? $wand->getWandType->type . ' - ' . $wand->value : $wand->value;
            })
            ->toArray();
        return implode(', ', $wands);
    }
}
