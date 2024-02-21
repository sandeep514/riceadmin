<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\WandModel;

class WandTypeModel extends Model
{
    protected $table = "wandType";
    protected $fillable = [ 'type' , 'order' , 'status' ];


    public function wand()
    {
        return $this->hasMany(WandModel::class, 'WandTypeId' , 'id' );
    }
}