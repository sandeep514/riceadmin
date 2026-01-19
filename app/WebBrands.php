<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\RiceBrandForm;
use App\WebBrandVariant;

class WebBrands extends Model
{
    protected $table = "web_brands";
    protected $fillable = ['name' ,'user_id','quality' ,'brand_year', 'address' ,'product_mode','description','logo','status'];


    // public function getAttachments()
    // {
    //     return $this->hasMany(Brandattachmentmodel::class , 'brand_id', 'id' );
    // }

    public function RiceName()
    {
        return $this->belongsTo(RiceName::class , 'quality' , 'id');
    }

    public function getVariants()
    {
        return $this->hasMany(WebBrandVariant::class , 'brand_id' , 'id');
    }
}