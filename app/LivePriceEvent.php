<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LivePriceEvent extends Model
{
    protected $fillable = [
        'quality_type_id',
        'quality_id',
        'quality_form_id',
        'event_date',
        'note',
        'status',
    ];

    public function qualityType()
    {
        return $this->belongsTo(RiceType::class, 'quality_type_id');
    }

    public function quality()
    {
        return $this->belongsTo(RiceName::class, 'quality_id');
    }

    public function qualityForm()
    {
        return $this->belongsTo(RiceForm::class, 'quality_form_id');
    }
}
