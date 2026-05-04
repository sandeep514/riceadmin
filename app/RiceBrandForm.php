<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RiceBrandForm extends Model
{
    protected $table = 'rice_brand_form';
    protected $fillable = ['form_name','type','order','status'];
    


    public static function riceForms(){
        $formsArray = [];
        $forms = self::get()->groupBy('type');
        foreach($forms as $key => $forms){
            $formsArray[$key] = $forms->pluck('form_name','id');
        }
        return $formsArray;
    }
}
