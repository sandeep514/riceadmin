<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class VendorUserMap extends Model
{
    protected $table = "vendor_user_map";
    protected $fillable = [
        'user_id',
        'type',
        'key',
        'value',
        'remarks',
        'is_sntc_recommended',
        'status',
    ];

    protected $casts = [
        'is_sntc_recommended' => 'integer',
    ];
}