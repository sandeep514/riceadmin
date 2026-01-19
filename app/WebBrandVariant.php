<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use App\Brandattachmentmodel;

class WebBrandVariant extends Model
{
    protected $table = "web_brand_variant";
    protected $fillable = ['variant','brand_id','quality_id','form_id','grade','packing','image','cut_image','status'];


    // public function getAttachments()
    // {
    //     return $this->hasMany(Brandattachmentmodel::class , 'brand_id', 'id' );
    // }

    public function qualityRel()
    {
        return $this->belongsTo(RiceName::class , 'quality_id' , 'id');
    }
    // public function formRel()
    // {
    //     return $this->belongsTo(RiceFormMilestone3::class , 'form_id' , 'id');
    // }

      public function formRel()
    {
        return $this->belongsTo(RiceBrandForm::class , 'form_id' , 'id');
    }

}