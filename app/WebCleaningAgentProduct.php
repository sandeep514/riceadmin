<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebCleaningAgentProduct extends Model
{
    protected $table = 'web_cleaning_agent_products';

    protected $fillable = [
        'user_id',
        'container_20_ft',
        'container_40_ft',
        'port_type',
        'icd_location',
        'port_location',
        'destination',
        'additional_information',
        'status',
    ];

    protected $casts = [
        'container_20_ft' => 'integer',
        'container_40_ft' => 'integer',
        'status' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function particulars()
    {
        return $this->hasMany(CleaningAgentParticularMap::class, 'product_id', 'id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /** Alias used by shared catalog helpers that expect "variants". */
    public function variants()
    {
        return $this->particulars();
    }
}
