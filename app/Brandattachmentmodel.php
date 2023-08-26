<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Brandattachmentmodel extends Model
{
    protected $table = "brand_attachment_milestone3";
    protected $fillable = ['brand_id' , 'attachment' , 'status'];
}
