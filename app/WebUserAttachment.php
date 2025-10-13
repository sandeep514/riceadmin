<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebUserAttachment extends Model
{
    protected $table = 'web_user_attachment';
    protected $fillable = ['user_id','panCard','farmer_id' ,'gstCard' ,'fssaiCard','status'];
}