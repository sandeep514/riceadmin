<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class NewsRunner extends Model
{
    public $table = "news_runner";
    protected $fillable = [ 'title','type','status' ];
}