<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RiceName extends Model
{
    protected $fillable = ['name','from_month','end_month','type','type_status','order'];

    public static function qualityNames(){
        $namesArray = [];
        $names = self::get()->groupBy('type');
        foreach($names as $key => $name){
            $namesArray[$key] = $name->pluck('name','id');
        }
        return $namesArray;
    }
    
    public static function qualityNamesForLivePrice(){
        $namesArray = [];
        $names = self::where('name' ,'!=' ,'PR - 47' )->where('name' , '!=', 'PR-14')->where('name' ,'!=' ,'Samba Mansoori')->get()->groupBy('type');
        foreach($names as $key => $name){
            $namesArray[$key] = $name->pluck('name','id');
        }
        return $namesArray;
    }

    public function wand()
    {
        return $this->hasMany(WandModel::class , 'id' , 'RiceNameId');
    }
}
