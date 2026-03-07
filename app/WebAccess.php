<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebAccess extends Model
{
    protected $table = 'web_access';
    
    protected $fillable = [
        'role_id',
        'category_id',
        'plan_id',
        'web_side_menu_id',
        'can_create',
        'can_read',
        'can_update',
        'can_delete',
        'status',
        'allowed_years'
    ];

    protected $casts = [
        'can_create' => 'boolean',
        'can_read' => 'boolean',
        'can_update' => 'boolean',
        'can_delete' => 'boolean',
        'status' => 'integer',
        'allowed_years' => 'array'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function plan()
    {
        return $this->belongsTo(WebPlanModel::class, 'plan_id', 'id');
    }

    public function webSideMenu()
    {
        return $this->belongsTo(WebSideMenu::class, 'web_side_menu_id', 'id');
    }
}
