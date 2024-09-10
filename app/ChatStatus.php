<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChatStatus extends Model
{
    protected $fillable = ['status'];
    protected $table = "chatStatus";
}