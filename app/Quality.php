<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Quality extends Model
{
    protected $fillable = ['name','type','category'];

    public function nameRel(){
        return $this->belongsTo(RiceName::class,'name','id');
    }

    public function typeRel(){
        return $this->belongsTo(RiceType::class,'type','id');
    }

    public function formRel(){
        return $this->belongsTo(RiceForm::class,'category','id');
    }

    public static function qualities(){
        $qualitiesList = self::with(['nameRel','typeRel','formRel'])->get();
        $qualityArray = [];
        foreach($qualitiesList as $key => $quality){
            $qualityArray[$quality->id] = ucwords(str_replace('-',' ',$quality->nameRel->type))
                .' - '.$quality->nameRel->name.' '.$quality->formRel->form_name.' ('.$quality->typeRel->name.')';
        }
        return $qualityArray;
    }

}
