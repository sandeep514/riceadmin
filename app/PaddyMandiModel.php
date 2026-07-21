<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaddyMandiModel extends Model
{
    protected $table = "paddyMandi";
    protected $fillable = ['mandi', 'state_id', 'status', 'order_no'];

    public function state_rel()
    {
        return $this->belongsTo(PaddyStateModel::class , 'state_id' , 'id' );
    }
}