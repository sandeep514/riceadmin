<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notification extends Model
{
    public $table = "notification";
    protected $fillable = [ 'user_id','title', 'message','userAppType','created_at' , 'updated_at' ];
    public $timestamps = true;


}