<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoryRoleMap extends Model
{
    protected $fillable = ['role' , 'category'];
    protected $table = "category_role_map";


    public function role_rel()
    {
        return $this->belongsTo(Role::class , 'role' , 'id');
    }

    public function category_rel()
    {
        return $this->belongsTo(Category::class, 'category' , 'id');
    }

}