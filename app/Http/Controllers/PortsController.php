<?php

namespace App\Http\Controllers;

use Session;
use App\Port;
use App\PortImages;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PortsController extends Controller
{
    public function index(Request $request)
    {
        $portImages = PortImages::get()->groupBy('port')->toArray();

        $todayPrice = collect();

        if ($request->has('date')) {
            $todayPrice = Port::where('route', '!=', '0')->where(DB::raw('date(created_at)'), $request->date)->get()->groupBy('state');
        } else {
            $lastUpdatedDate = Port::orderBy('created_at', 'DESC')->first();


            if ($lastUpdatedDate != null) {
                $dateCreate = date_create($lastUpdatedDate->created_at);
                $formatedDate = date_format($dateCreate, 'Y/m/d');

                // $todayPrice = Port::whereDate('created_at' , $formatedDate)->get()->groupBy('state');

                /**
                 * Changes By Jaskaran Singh
                 **/
                // where('route','!=','0')->
                // ->groupBy('state')
                // where('state','=','CHHATTISGARH')
                $todayPrice = Port::where('route','!=','0')->get()->groupBy('state');
            }
        }
        return view('ports.create', ['prices' => $todayPrice,'portImages' => $portImages]);
    }

    public function save(Request $request)
    {
        $portsOrder = Port::distinct('state_order' , 'state')->where('state_order' ,'!=', null)->pluck('state_order' , 'state');
        
        $data = Port::where('state_order','!=',null)->pluck('state','state_order')->all();
    
        $requestData = $request->except(['_token', 'date']);
        $checkIfHasTodayPorts = Port::whereDate('created_at', Carbon::today()->format('Y-m-d'))->first();
        Port::whereDate('created_at', Carbon::today()->format('Y-m-d'))->delete();
        
        foreach($requestData as $k => $v){
            foreach($v as $kk => $vv){
                if( $vv != null ){
                    $price = $vv;
                }else{
                    $price = 0;
                }
                $create = ['state' => $k , 'route' => $kk , 'price' => $price , 'status' => 1];
                Port::create($create);
            }
        }
        foreach($portsOrder as $k => $v){
            $getBanner = Port::where('state' , $k)->first();
            if( $getBanner != null ){
                $getBannerUrl = $getBanner->banner; 
                Port::where('state' , $k)->update(['state_order' => $v , 'banner' => $getBannerUrl ]);
            }else{
                Port::where( 'state' , $k )->update([ 'state_order' => $v ]);
            }
        }
        // if ($checkIfHasTodayPorts != null) {
        //     foreach ($requestData as $state => $routes) {
        //         foreach ($routes as $route => $price) {
        //             $isPortPriceAlreadyExists = Port::where(DB::raw('date(created_at)'), Carbon::today()->format('Y-m-d'))->where(['state' => $state, 'route' => $route])->update(['price' => $price]);
        //         }
        //     }
        // } else {
        //     foreach ($requestData as $state => $routes) {
        //         foreach ($routes as $route => $price) {
        //             $isPortPriceAlreadyExists = Port::where(DB::raw('date(created_at)'), Carbon::today()->format('Y-m-d'))->firstOrNew(['state' => $state, 'route' => $route]);
        //             $isPortPriceAlreadyExists->state = $state;
        //             $isPortPriceAlreadyExists->route = $route;
        //             $isPortPriceAlreadyExists->price = $price;
        //             $isPortPriceAlreadyExists->save();
        //         }
        //     }
        // }
        
        // foreach($data as $key => $value ){
        //     Port::where('state',$value)->update(['state_order'=>$key]);
        // }

        Session::flash('success', 'Success|Ports saved successfully!');
        return back();
    }
    public function uploadStateImage(Request $request)
    {
        $file = $request->file('file');
        $filename = $file->getCLientOriginalName();
        $fileExtension = $file->getCLientOriginalExtension();
        
        $acceptedFileType = ['png' , 'jpg', 'jpeg'];

        if( in_array($fileExtension , $acceptedFileType) ) {
            PortImages::updateOrCreate(['port' => $request->image_state] , [ 'attachment' => $filename,'status' => 1]);
            $destinationPath = 'uploads/port';
            $file->move($destinationPath,$filename);

            Session::flash('sucess' , 'Port Image uploaded successfully.');
        }else{
            Session::flash('error', 'File not supported.');
        }
        return back();;
    }
}
