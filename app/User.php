<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'country',
        'zip_code',
        'import_port',
        'contact_person_name',
        'address',
        'phone',
        'mobile',
        'gst_no',
        'city',
        'api_token',
        'state',
        'companyname',
        'role',
        'usd_role',
        'bagCategory',
        'message',
        'otp',
        'status',
        'has_validation',
        'is_viewed_by_admin',
        'expired_on',
        'is_usd_active',
        'is_INR_active',
        'is_active_by_admin',
        'transaction_id',
        'planId',
        'user_from',
        'userType',
        'stripe_customer_id',
        'stripe_payment_method'
    ];

    public function routeNotificationForFcm()
    {
        return $this->user_token;
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role_rel()
    {
        return $this->belongsTo(Role::class, 'role', 'id');
    }

    public function role_rel_usd()
    {
        return $this->belongsTo(Role::class, 'usd_role', 'id');
    }

    public function field_runner_rel()
    {
        return $this->belongsTo(FieldRunner::class, 'id', 'user_id');
    }

    public static function sellers()
    {
        return self::whereRole(4)->pluck('name', 'id');
    }

    public static function buyers()
    {
        return self::whereRole(5)->pluck('name', 'id');
    }

    public function seller_rel()
    {
        return $this->belongsTo(Seller::class, 'id', 'user_id');
    }

    public function buyer_rel()
    {
        return $this->belongsTo(Buyer::class, 'id', 'user_id');
    }
    public function bagVendor()
    {
        return $this->belongsTo(Vendorcategory::class, 'bagCategory', 'id');
    }
    public function getHotDealStatusBySeller()
    {
        return $this->belongsTo(HotDealAccept::class, 'buyer_id', 'id');
    }
    public function getWebPersonalDetails(){
        return $this->belongsTo(WebPersonalDetails::class  , 'id' , 'user_id');
    }
    public function getWebBusinessDetails(){
        return $this->belongsTo(WebBusinessDetails::class  , 'id' , 'user_id');
    }
    public function getWebUserAttachment(){
        return $this->belongsTo(WebUserAttachment::class  , 'id' , 'user_id');
    }
    public function getWebUserSubscription(){
        return $this->hasOne(WebUserSubscriptionModel::class  , 'user_id' , 'id')->orderBy('id' , 'desc');
        // return $this->hasOne(WebUserSubscriptionModel::class  , 'user_id' , 'id')->orderBy('id' , 'desc')->whereDate('period_end' , '>=' , Carbon::now()->format('Y-m-d'));
    }
}
