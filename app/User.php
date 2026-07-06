<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
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
        'is_deactivated',
        'transaction_id',
        'planId',
        'user_from',
        'userType',
        'can_edit_by_admin',
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
        'can_edit_by_admin' => 'integer',
        'is_deactivated' => 'integer',
    ];

    public function isDeactivated(): bool
    {
        if (! Schema::hasColumn($this->getTable(), 'is_deactivated')) {
            return false;
        }

        return (int) $this->getAttribute('is_deactivated') === 1;
    }

    /**
     * Admin-approved user who was later deactivated (not pending / unverified status).
     */
    public function isAdminDeactivated(): bool
    {
        return $this->isDeactivated()
            && (int) ($this->getAttribute('is_active_by_admin') ?? 0) === 1;
    }

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

    public function interestedMaps()
    {
        return $this->hasMany(UserInterestedMap::class, 'user_id');
    }

    /**
     * 1 = user asked SNTC to approve / manage search experience (admin may edit interests).
     * 0 = user manages interests themselves (admin read-only).
     */
    public function allowsAdminInterestManagement(): bool
    {
        if (! Schema::hasColumn($this->getTable(), 'can_edit_by_admin')) {
            return false;
        }

        return (int) $this->getAttribute('can_edit_by_admin') === 1;
    }

    public function canEditByAdminFlag(): int
    {
        if (! Schema::hasColumn($this->getTable(), 'can_edit_by_admin')) {
            return 0;
        }

        return (int) $this->getAttribute('can_edit_by_admin');
    }
}
