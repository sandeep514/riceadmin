<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\BagVendors;

class WebVendorCategory extends Model
{
    protected $table = "web_vendorcategory";
    protected $fillable = ['name','status'];

    public function getVendorList()
    {
        return $this->hasMany(BagVendors::class , 'vendor_type' , 'id' );
    }

}