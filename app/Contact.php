<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'contact_us';
    protected $fillable = ['phone' , 'email', 'status'];
}
