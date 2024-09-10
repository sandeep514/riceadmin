<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RiceForm extends Model
{
    protected $fillable = ['form_name','type'];
    public static $grade = [1=>'' ,2=>'A' , 3=>'A+'];


    public static function riceForms(){
        $formsArray = [];
        $forms = self::get()->groupBy('type');
        foreach($forms as $key => $forms){
            $formsArray[$key] = $forms->pluck('form_name','id');
        }
        return $formsArray;
    }
}
