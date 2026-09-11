<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebClearingAgentProduct extends Model
{
    protected $table = 'web_clearing_agent_products';

    protected $fillable = [
        'user_id',
        'container_size_id',
        'container_size',
        'port_type_id',
        'port_type',
        'icd_location_id',
        'icd_location',
        'indian_port_id',
        'port_location',
        'destination_port_id',
        'destination',
        'additional_information',
        'status',
    ];

    protected $casts = [
        'container_size_id' => 'integer',
        'container_size' => 'integer',
        'port_type_id' => 'integer',
        'icd_location_id' => 'integer',
        'indian_port_id' => 'integer',
        'destination_port_id' => 'integer',
        'status' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function containerSizeRel()
    {
        return $this->belongsTo(VendorContainerSize::class, 'container_size_id', 'id');
    }

    public function portTypeRel()
    {
        return $this->belongsTo(VendorPortType::class, 'port_type_id', 'id');
    }

    public function icdLocationRel()
    {
        return $this->belongsTo(VendorIcdLocation::class, 'icd_location_id', 'id');
    }

    public function indianPortRel()
    {
        return $this->belongsTo(VendorIndianPort::class, 'indian_port_id', 'id');
    }

    public function destinationPortRel()
    {
        return $this->belongsTo(VendorDestinationPort::class, 'destination_port_id', 'id');
    }

    public function particulars()
    {
        return $this->hasMany(ClearingAgentParticularMap::class, 'product_id', 'id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /** Alias used by shared catalog helpers that expect "variants". */
    public function variants()
    {
        return $this->particulars();
    }
}
