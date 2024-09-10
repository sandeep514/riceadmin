<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FieldRunner extends Model
{
    protected $fillable = ['user_id','zone','designation'];

    public function designation_rel(){
        return $this->belongsTo(Designation::class,'designation','id');
    }
}
