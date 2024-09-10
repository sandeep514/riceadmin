<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $table = "gallery";
    protected $fillable = ['title', 'description' ,'attachment' ,'spec' ,'amount', 'type','attachment2'];
}
