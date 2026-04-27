<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebRiceFormMap extends Model
{
    protected $table = 'web_rice_form_map';

    protected $fillable = ['rice_name_id', 'group_name', 'form_ids'];

    protected $casts = [
        'form_ids' => 'array',
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
        $forms = RiceForm::whereIn('id', $this->form_ids)->pluck('form_name')->toArray();
        return implode(', ', $forms);
    }
}
