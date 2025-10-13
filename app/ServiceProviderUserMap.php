<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ServiceProviderUserMap extends Model
{
    protected $table = "service_provider_user_map";
    protected $fillable = [
        'user_id',
        'type',
        'key',
        'value',
        'remarks',
        'status'
    ];
}