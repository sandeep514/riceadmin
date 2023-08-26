<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WandTypeModel extends Model
{
    protected $table = "wandType";
    protected $fillable = [ 'type' , 'order' , 'status' ];
}
