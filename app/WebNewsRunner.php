<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class WebNewsRunner extends Model
{
    public $table = "web_news_runner";
    protected $fillable = [ 'title','description','type','newsType','news_date','status' ];

    protected $casts = [
        'news_date' => 'date',
    ];
}