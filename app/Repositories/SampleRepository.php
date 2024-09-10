<?php

namespace App\Repositories;
use App\Sample;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SampleRepository
{
    public static function saveSample($request){
        $sampleModel = new Sample;
        $sampleModel->fill($request->except(['photo','date']));
        if($request->hasFile('photo')){
            $fileExtension = $request->file('photo')->getClientOriginalExtension();
            $randomName = Str::random(10);
            $request->file('photo')->move('sample-images',$randomName.'.'.$fileExtension);
            $sampleModel->photo = $randomName.'.'.$fileExtension;
        }
        $sampleModel->date = Carbon::parse($request->date)->format('Y-m-d');
        $sampleModel->user_id = Auth::user()->id;
        $sampleModel->save();
        return $sampleModel;
    }

    public static function updateSample($request, $id){

        $sampleModel = Sample::find($id);
        $sampleModel->fill($request->except(['photo','date']));
        if($request->hasFile('photo')){
            $fileExtension = $request->file('photo')->getClientOriginalExtension();
            $randomName = Str::random(10);
            $request->file('photo')->move('sample-images',$randomName.'.'.$fileExtension);
            $sampleModel->photo = $randomName.'.'.$fileExtension;
        }
        $sampleModel->date = Carbon::parse($request->date)->format('Y-m-d');
        $sampleModel->save();
        return $sampleModel;
    }

    public static function deleteSample($id){
        $sampleModel = Sample::find($id);
        $sampleModel->delete();
        return $sampleModel;
    }
}
