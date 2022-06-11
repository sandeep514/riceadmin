<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','country','zip_code','import_port','contact_person_name','address','phone','mobile','gst_no','city','api_token','state','companyname','role','bagCategory','otp','status','expired_on','is_usd_active'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role_rel(){
        return $this->belongsTo(Role::class,'role','id');
    }

    public function field_runner_rel(){
        return $this->belongsTo(FieldRunner::class,'id','user_id');
    }

    public static function sellers(){
        return self::whereRole(4)->pluck('name','id');
    }

    public static function buyers(){
        return self::whereRole(5)->pluck('name','id');
    }

    public function seller_rel(){
        return $this->belongsTo(Seller::class,'id','user_id');
    }

    public function buyer_rel(){
        return $this->belongsTo(Buyer::class,'id','user_id');
    }
    public function bagVendor(){
        return $this->belongsTo(Vendorcategory::class,'bagCategory','id');
    }
}
