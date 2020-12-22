<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
class Message extends Model
{
    protected $table = "messages";
    protected $fillables = ['from', 'to', 'seen', 'message', 'status'];
	
    public function user_rel(){
        return $this->belongsTo(User::class,'from','id');
    }

}
