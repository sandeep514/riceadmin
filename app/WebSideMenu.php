<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebSideMenu extends Model
{
    protected $table = 'web_side_menu';
    
    protected $fillable = [
        'title',
        'sub_title',
        'slug',
        'create_url',
        'read_url',
        'update_url',
        'delete_url',
        'status',
        'sort_order'
    ];

    protected $casts = [
        'status' => 'integer',
    ];
}

