<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RiceFormMilestone3 extends Model
{
    protected $fillable = ['name','order','status'];
    protected $table = "rice_form_milestone3";
    public static function riceForms(){
        $formsArray = [];
        $forms = self::get()->groupBy('type');
        foreach($forms as $key => $forms){
            $formsArray[$key] = $forms->pluck('form_name','id');
        }
        return $formsArray;
    }
}
