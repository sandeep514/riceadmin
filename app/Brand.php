<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use App\Brandattachmentmodel;

class Brand extends Model
{
    protected $table = "brands_milestone3";
    protected $fillable = ['name', 'image','status'];


    public function getAttachments()
    {
        return $this->hasMany(Brandattachmentmodel::class , 'brand_id', 'id' );
    }

}