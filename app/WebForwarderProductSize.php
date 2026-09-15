<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebForwarderProductSize extends Model
{
    protected $table = 'web_forwarder_product_sizes';

    protected $fillable = [
        'product_id',
        'container_size_id',
        'size',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'container_size_id' => 'integer',
        'size' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(WebForwarderProduct::class, 'product_id', 'id');
    }

    public function containerSizeRel()
    {
        return $this->belongsTo(VendorContainerSize::class, 'container_size_id', 'id');
    }
}
