<?php

namespace App\Http\Controllers;

use App\DataTables\SamplesDataTable;
use App\Http\Requests\SampleRequest;
use App\Sample;
use App\Notification;
use App\Services\SampleService;
use Illuminate\Http\Request;
use Session;
use App\User;
use App\HotDealNotification;
use App\QualityMaster;
use App\USD_defaultmaster;
use App\HotDealAccept;
use App\PublicPacking;
use Carbon\Carbon;
use Excel;

class PublicPackingMasterController extends Controller
{
    public function index()
    {
        $data = PublicPacking::get();
        return View('PublicPacking.create' , compact('data'));
    }
    public function save(Request $request)
    {
        $excel = Excel::toArray( [] , $request->file('file'));
        $processedData = [];
        if( count($excel) > 0 ){
            if( count($excel[0]) > 2 ){
                foreach( $excel[0] as $k => $v ){
                    if( count(array_filter($v)) > 0 ){
                        if( count(array_filter($v)) > 1 ){
                            if( !in_array( 'size' , array_filter($v)) ) {     
                                if( count(array_filter($v)) == 3 ){
                                    if( $k > 1 ){
                                        $processedData[] = [
                                            'size'      => $v[0],
                                            'packing'  => $v[1],
                                            'order'  => $v[2]
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if( count($processedData) > 0 ){
            // USD_defaultmaster::truncate();
            // foreach($processedData as $k => $v){
                PublicPacking::insert($processedData);
                Session::flash('message' , 'Route updated successfully');
            // }
        }
        return back();

    }
}