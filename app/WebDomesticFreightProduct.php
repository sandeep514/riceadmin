<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebDomesticFreightProduct extends Model
{
    protected $table = 'web_domestic_freight_products';

    public const STATUS_PENDING = 0;

    public const STATUS_VERIFIED = 1;

    protected $fillable = [
        'user_id',
        'state_id',
        'state',
        'city_id',
        'city',
        'destination_id',
        'destination',
        'truck_size_id',
        'truck_size',
        'price',
        'admin_message',
        'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'state_id' => 'integer',
        'city_id' => 'integer',
        'destination_id' => 'integer',
        'truck_size_id' => 'integer',
        'status' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function stateRel()
    {
        return $this->belongsTo(DomesticVendorState::class, 'state_id', 'id');
    }

    public function cityRel()
    {
        return $this->belongsTo(DomesticVendorCity::class, 'city_id', 'id');
    }

    public function destinationRel()
    {
        return $this->belongsTo(DomesticVendorDestination::class, 'destination_id', 'id');
    }

    public function truckSizeRel()
    {
        return $this->belongsTo(DomesticVendorTruckSize::class, 'truck_size_id', 'id');
    }
}
