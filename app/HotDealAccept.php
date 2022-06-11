<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class HotDealAccept extends Model
{
    public $table = "hotdealaccept";
    protected $fillable = [ 'hotdeal_id','buyer_id','status' ];
}