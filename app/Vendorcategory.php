<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\BagVendors;

class Vendorcategory extends Model
{
    protected $table = "vendorcategory";
    protected $fillable = ['name','status'];

    public function getVendorList()
    {
        return $this->hasMany(BagVendors::class , 'vendor_type' , 'id' );
    }

}