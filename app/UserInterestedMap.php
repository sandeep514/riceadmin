<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserInterestedMap extends Model
{
    protected $table = 'user_interested_map_table';

    protected $fillable = [
        'user_id',
        'rice_name_id',
        'form_id',
        'grade',
        'status',
    ];
}
