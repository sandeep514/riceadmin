<?php


namespace App\Repositories;


use App\Imports\QualityImport;
use App\Quality;
use Illuminate\Support\Str;
use Excel;

class QualityRepository
{
    public static function saveQuality($request){
        $qualityModel = new Quality;
        $qualityModel->fill($request->all());
        $qualityModel->save();
        return $qualityModel;
    }

    public static function updateQuality($request, $id){
        $qualityModel = Quality::find($id);
        $qualityModel->fill($request->all());
        $qualityModel->save();
        return $qualityModel;
    }

    public static function deleteQuality($id){
        $qualityModel = Quality::find($id);
        $qualityModel->delete();
        return $qualityModel;
    }

    public static function importQuality($request){
        $import = new QualityImport;
        Excel::import($import,request()->file('file'));
        return $import->data;
    }
}
