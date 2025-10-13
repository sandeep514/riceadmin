<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TestimonialVideo extends Model
{
    protected $table = 'testimonial_video';
    protected $fillable = ['id','title','file','status'];
}