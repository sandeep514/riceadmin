<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RiceName extends Model
{
    protected $fillable = ['name','from_month','end_month','type','type_status','order','status'];

    public static function qualityNames(){
        $namesArray = [];
        $names = self::orderedForSelect()->get()->groupBy('type');
        foreach($names as $key => $name){
            $namesArray[$key] = $name->pluck('name','id');
        }
        return $namesArray;
    }
    
    public static function qualityNamesForLivePrice(){
        $namesArray = [];
        $names = self::orderedForSelect(true)
            ->where('name', '!=', 'PR - 47')
            ->where('name', '!=', 'PR-14')
            ->where('name', '!=', 'Samba Mansoori')
            ->get()
            ->groupBy('type');
        foreach($names as $key => $name){
            $namesArray[$key] = $name->pluck('name','id');
        }
        return $namesArray;
    }

    /**
     * Master list order: type group, then rice_names.order, then id.
     */
    public static function orderedForSelect(bool $activeOnly = false)
    {
        $query = self::query();

        if ($activeOnly) {
            $query->where('status', 1);
        }

        return $query
            ->orderBy('type')
            ->orderByRaw('COALESCE(`order`, 999999) ASC')
            ->orderBy('id', 'ASC');
    }

    public function wand()
    {
        return $this->hasMany(WandModel::class , 'id' , 'RiceNameId');
    }
}
