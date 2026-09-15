<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebForwarderProduct extends Model
{
    protected $table = 'web_forwarder_products';

    protected $fillable = [
        'user_id',
        'port_type_id',
        'port_type',
        'icd_location_id',
        'icd_location',
        'indian_port_id',
        'port_location',
        'region_id',
        'country_id',
        'destination_port_id',
        'destination',
        'additional_information',
        'admin_message',
        'status',
    ];

    protected $casts = [
        'port_type_id' => 'integer',
        'icd_location_id' => 'integer',
        'indian_port_id' => 'integer',
        'region_id' => 'integer',
        'country_id' => 'integer',
        'destination_port_id' => 'integer',
        'status' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
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

    public function regionRel()
    {
        return $this->belongsTo(VendorDestinationRegion::class, 'region_id', 'id');
    }

    public function countryRel()
    {
        return $this->belongsTo(VendorDestinationCountry::class, 'country_id', 'id');
    }

    public function destinationPortRel()
    {
        return $this->belongsTo(VendorDestinationPort::class, 'destination_port_id', 'id')
            ->with(['region', 'country']);
    }

    public function containerSizes()
    {
        return $this->hasMany(WebForwarderProductSize::class, 'product_id', 'id')
            ->orderBy('size')
            ->orderBy('id');
    }

    public function charges()
    {
        return $this->hasMany(WebForwarderCharge::class, 'product_id', 'id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /** Alias used by shared catalog helpers that expect "variants". */
    public function variants()
    {
        return $this->charges();
    }

    public function particulars()
    {
        return $this->charges();
    }
}
