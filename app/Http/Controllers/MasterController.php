<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RiceForm;
use App\RiceName;
use App\RiceType;
use App\Port;
use App\LivePrice;
use Session;
use Carbon\Carbon;
use App\User;
use App\FreeTrialMonths;
use App\Version;
use App\QualityMaster;
use App\USD_prices;
use App\USD_defaultmaster;
use App\Defaultvalue;
use App\SellQueriesINR;
use App\BuyQueriesINR;
use Mail;

class MasterController extends Controller
{
	public function index(){
		return view('master.create');	
	}

	public function listRiceType(){
		$riceForm = RiceForm::where('status' , 1)->get();
		return view('master.listRiceForm' , compact('riceForm'));
	}

	public function getRiceTypeById($riceTypeId){
		$riceForm = RiceForm::where('id' , $riceTypeId)->first();
		return view('master.editRiceForm' , compact('riceForm'));

	}

	public function updateRiceTypeById(Request $request)
	{
		if( !$request->has('riceType') || $request->riceType == null ){
			Session::flash('message' , 'Form Name is required.');
			return back();
		}
		if( !$request->has('name') || $request->name == null ){
			Session::flash('message' , 'Type field is required.');
			return back();
		}
		RiceForm::where('id' , $request->id)->update([ 'form_name' => $request->name , 'type' => $request->riceType ]);
		return back();
	}


	public function createRiceType(Request $request){
		if( !$request->has('riceType') || $request->riceType == null ){
			Session::flash('message' , 'Form Name is required.');
			return back();
		}
		if( !$request->has('name') || $request->name == null ){
			Session::flash('message' , 'Type field is required.');
			return back();
		}

		RiceForm::create(['form_name' => $request->name , 'type' => $request->riceType ]);
		return back();
	}

	public function deleteRiceType($id){
		RiceForm::where('id' , $id)->delete();
		return back();
	}
	public function deleteStatePort($state){
		$portData = Port::where('id' , $state)->first();
		$portState = $portData->state;
		Port::where('state' , $portState)->delete();
		return back();
	}
	public function listRiceQuality(){
		$riceName = RiceName::get();
		return view('master.listRiceName', compact('riceName'));
	}

	public function createRiceQuality(Request $request){
		if( !$request->has('riceType') || $request->riceType == null ){
			Session::flash('message' , 'Form Name is required.');
			return back();
		}
		if( !$request->has('name') || $request->name == null ){
			Session::flash('message' , 'Type field is required.');
			return back();
		}

		RiceName::create(['name' => $request->name , 'type' => $request->riceType , 'type_status' => ($request->riceType == 'basmati')? 1: 2 ]);
		return back();
	}

	public function getCityById($id)
	{
		$decoded = base64_decode($id);
		$livePrices = LivePrice::where('state' , $decoded)->first();
		return view('master.editPriceState' , compact('livePrices'));
	}

	public function updateCityById(Request $request)
	{
		$livePrice = LivePrice::where(['id' => $request->id])->first();

		LivePrice::where(['state' => $livePrice->state])->update(['state' => $request->state]);
		Session::flash('message' , 'Price State updated successfully');
		return redirect()->route('master.list.city');
	}
	
	public function getRicequalityById($riceQualityId)
	{
		$riceName = RiceName::where('id' , $riceQualityId)->first();
		return view('master.editRiceName', compact('riceName'));
	}
	
	public function updateRiceQualityById(Request $request)
	{
		if( !$request->has('riceType') || $request->riceType == null ){
			Session::flash('message' , 'Form Name is required.');
			return back();
		}
		if( !$request->has('name') || $request->name == null ){
			Session::flash('message' , 'Type field is required.');
			return back();
		}
		RiceName::where('id' , $request->id)->update([ 'name' => $request->name , 'type' => $request->riceType ]);
		return back();
	}
	
	public function deleteRiceQuality($id){
		RiceName::where( 'id' ,$id )->delete();
		return back();
	}

	public function listCity(){
		$cities = LivePrice::get()->pluck( 'status' ,'state');
		return view('master.listCity' , compact('cities'));
	}

	public function deleteCity($cityId)
	{
		$cityName = base64_decode($cityId);

	}

	public function editCity(Request $request)
	{
		$cityName = base64_decode($cityId);
		// LivePrice::where('state' , $cityName)->
		return view('master.editCity' , compact('cityName'));
	}

	public function createCity(Request $request){
		$lastCityOrder = LivePrice::orderBy('state_order' , 'desc')->first();
		LivePrice::create(['name' => 0,'form' => 0 , 'state' => $request->name , 'up_down' => 'up','state_order' => ((int)$lastCityOrder->state_order+1)]);
		
		Session::flash('message' , 'City added successfully');
		return back();
	}

	public function statusCity($cityId)
	{
		$cityName = base64_decode($cityId);

		$livePrices = LivePrice::where('state' , $cityName)->first();
		if( $livePrices->status == 0 ){
			LivePrice::where('state' , $cityName)->update(['status' => 1]);
		}else{
			LivePrice::where('state' , $cityName)->update(['status' => 0]);
		}

		Session::flash('message' , 'Status changes successfully');
		return back();
	}

	public function createPort(Request $request)
	{
		$request->validate([
			'state' => 'required|unique:ports',
		]);
		
        $availableRoute =   Port::where('route' ,'!=', '0')->get()->groupBy('route');
		$data  = Port::orderBy('state_order','DESC')->first();
		$order = 0;
		if($data != null){
			$order = $data->state_order;
		}
        if($availableRoute->keys()->count() > 0 ){
            foreach( $availableRoute->keys() as $k => $v ){
                Port::create([ 'state' => $request->state,'route' => $v,'price' => 0,'state_order' => $order+1]);    
            }    
        }else{
            Port::create([ 'state' => $request->state,'route' => '0','price' => 0,'state_order' => $order+1]);
        }
        
		Session::flash('message' , 'City added successfully');
		return back();
	}

	public function listState()
	{

	}

	public function listRoute()
	{
		$ports = Port::where('route' , '!=' , '0')->get()->pluck( 'id' , 'route' );
		return view('master.listRoutes' , compact('ports'));
	}

	public function deleteRoute($id)
	{
		$getPortROute = Port::select('route')->where('id' , $id)->first();
		if( $getPortROute != null ){
			$routeName = $getPortROute->route;
			Port::where('route' , $routeName)->delete();
		}
		Session::flash('message' , 'Route deleted successfully.');
		return back();
	}

	public function getPortROute($id)
	{
		$getPortRoute = Port::where('id' , $id)->first();
		if( $getPortRoute != null ){
			return view('editTransportRoute' , compact('getPortRoute'));
		}
		Session::flash('error' , 'Something went wrong');
		return back();
	}

	public function updateTransportRoute(Request $request)
	{
		$port = Port::where( 'id' , $request->id )->first();

		$routeExplode = explode(' ', $request->route);
		$routeImplode = implode('_', $routeExplode);
		$portIds = Port::where(['route' => $port->route])->get()->map(function($query){
			return $query->id;
		});
		$portIdsArray = $portIds->toArray();

		Port::whereIn( 'id' , $portIdsArray )->update([ 'route' => $routeImplode ]);
		Session::flash('message' , 'Route updated successfully');
		return back();
	}

	public function getTransportRoute($routeId)
	{

		$port = Port::where('id' , $routeId)->first();
		$explodedArray = explode('_',$port->route);
		$implodedString = implode(' ' , $explodedArray);
		$port->route = $implodedString;

		return view('master.editTransportRoute' , compact('port'));
	}

	public function editPort($stateid)
	{
		$port = Port::where('id' , $stateid)->first();
		if( $port != null ){
			$portState = $port;
			return view('master.editState' , compact('portState'));
		}

		return back();
	}

	public function updatePort(Request $request)
	{

		$port = Port::where('id' , $request->id)->first();
		
		Port::where('state' , $port->state)->update(['state' => $request->state]);
		
		if( $request->has('uploadBanner') ){
		    if( $request->uploadBanner != null ){
		        $file = $request->file('uploadBanner');
        		$filename = rand(1111,9999).'_'.$file->getClientOriginalName();
        		$fileExtention = $file->getclientoriginalextension();
        		
                if( $fileExtention == 'jpg' || $fileExtention == "JPEG" || $fileExtention == 'png' || $fileExtention == "PNG" ){
                    $destination = 'uploads/banner';
            		$file->move($destination , $filename);
            		Port::where([ 'state' => $port->state ])->update(['banner' => $filename]);
                }else{
                    return back()->withErrors(['fileFormat' => 'File Extention must be jpg or png.']);
                }
		    }
		}
		
		return back();
	}

	public function createRoute(Request $request)
	{
		$request->validate([
			'route' => 'required',
		]);

		$explodedArray = explode(' ',$request->route);
		$implodedString = implode('_' , $explodedArray);
		$request->route = $implodedString;

		$listPorts = Port::get()->pluck('id' , 'state');
		foreach($listPorts as $k => $v){
			Port::create([ 'state' => $k ,'route' => $request->route,'price' => 0]);
		}
		Session::flash('message' , 'Port added successfully');
		return back();
	}

	public function listPort() {
		$ports = Port::get()->pluck( 'id' , 'state' );
		return view('master.listState' , compact('ports'));	
	}
	
	public function changedateofexistinguser()
	{
		return view('trialPeriod.index');
	}

	public function saveTrialPeriod(Request $request)
	{
		$carbonNow = Carbon::now()->format('Y-m-d');
		$updatedDate = Carbon::now()->addMonths($request->trialPeriod)->format('Y-m-d');
		User::where('id' ,'!=' , 1)->where( 'expired_on'  ,'<=', Carbon::now()->addMonths(1)->format('Y-m-d'))->update(['expired_on' => $updatedDate]);
 		return back();
	}
	
	public function changeTrialPeriodDate()
	{
		$trialMonth = FreeTrialMonths::where('id' , 1)->first();
		return view('trialMonth.index' ,compact('trialMonth'));
	}

	public function trialPeriodMonthSave(Request $request){
		FreeTrialMonths::where('id' , 1)->update(['month' => $request->trialPeriod]);
		$trialMonth = FreeTrialMonths::where('id' , 1)->first();
		return back();
	}

	public function createVersion(){
		$latestVersion = Version::orderBy('id' , 'desc')->first();
        return View('version.create' , compact('latestVersion')) ;
	}

	public function saveVersion(Request $request)
	{
		$request->validate([
			'version' => 'required',
		]);
		$version = Version::create([
			'version' => $request->version,
		]);
		return back();
	}
	
	public function createCalculator()
	{
		$threeDaysBackDate = Carbon::now()->format('Y-m-d');

		$query = QualityMaster::get()->map(function($query){
			return $query->id;
		});
		$usdPrice = USD_prices::with(['getRiceQuality','getUSDDefaultMaster'])->whereDate('created_at', '>=', $threeDaysBackDate)->whereIn('rice' , $query)->orderBy('created_at' , 'DESC')->get();


		// $usdPrice = [];
		// foreach($query as $k => $v){
		// 	$usdPricing = USD_prices::with(['getRiceQuality','getUSDDefaultMaster'])->where('rice' , $v)->orderBy('created_at' , 'DESC')->first();
		// 	if( $usdPricing != null){
		// 		$usdPrice[] = $usdPricing;
		// 	}
		// }


		// $data2 = $query->toArray();

		// $processedData = [];
		// $data = USD_prices::get()->map(function ($query) use ($data2){
		// 	return $query->where('rice' , $data2)->orderBy('created_at' , 'DESC')->first();
		// });


		// $uniqueArray = array_unique($data);
		// $arrayValues = array_values($uniqueArray);

		// $lastSecondDate = 0;

		// if( count($arrayValues) > 1 ){
		// 	$lastSecondDate = $arrayValues[(count($arrayValues) - 2)];
		// 	$usdPrice = USD_prices::with(['getRiceQuality','getUSDDefaultMaster'])->orderBy('id' , 'DESC')->whereDate('created_at' , '>=' , $lastSecondDate)->where('status' , 1)->get();
		// }elseif( count($arrayValues) == 1 ){
		// 	$lastSecondDate = $arrayValues[(count($arrayValues) - 1)];
		// 	$usdPrice = USD_prices::with(['getRiceQuality','getUSDDefaultMaster'])->orderBy('id' , 'DESC')->whereDate('created_at' , '>=' , $lastSecondDate )->where('status' , 1)->get();
		// }else{
		// 	$usdPrice = USD_prices::with(['getRiceQuality','getUSDDefaultMaster'])->orderBy('id' , 'DESC')->where('status' , 1)->get();
		// }

		$riceName = QualityMaster::all();
		$defaultValue = Defaultvalue::first();
		$dollarRate = $defaultValue->dollarvalue;
		
		return view('calculator.create' , compact('riceName' , 'usdPrice','dollarRate','defaultValue'));
	}

	public function USDPriceReport()
	{
		$threeDaysBackDate = Carbon::now()->subDays(2)->format('Y-m-d');

		$query = QualityMaster::get()->map(function($query){
			return $query->id;
		});
		$usdPrice = USD_prices::with(['getRiceQuality','getUSDDefaultMaster'])->whereIn('rice' , $query)->orderBy('created_at' , 'DESC')->get();

		$riceName = QualityMaster::all();
		$defaultValue = Defaultvalue::first();
		$dollarRate = $defaultValue->dollarvalue;
		
		return view('reports.index' , compact('riceName' , 'usdPrice','dollarRate'));
	}
	

	public function editRiceQualityUSD($id)
	{
		$riceName = QualityMaster::all();
		$usdPrice = USD_prices::with(['getRiceQuality','getUSDDefaultMaster'])->where('id' , $id)->first();

		$defaultValue = Defaultvalue::first();

		$dollarRate = $defaultValue->dollarvalue;

		return view('calculator.edit' , compact('riceName' , 'usdPrice','dollarRate'));
	}
	public function saveCalculator(Request $request)
	{
		$riceName 		= $request->riceName;
		$ricemin 		= $request->ricemin;
		$ricemax 		= $request->ricemax;
		$transportmin 	= $request->portmin;
		$transportmax 	= $request->portmax;
		$category 		= $request->bag;
		$charges 		= $request->charges;
		$dollarrate 	= $request->dollar;
		$percentageValue= $request->percentage;

		if($ricemin == ''){
            $ricemin = 0;
        }
        if($ricemax == ''){
            $ricemax = 0;
        }
        if($transportmin == ''){
            $transportmin = 0;
        }
        if($transportmax == ''){
            $transportmax = 0;
        }
        if( $ricemax < $ricemin ){
            // alert('Rice max price should be greater than Rice min price.');
            return false;
        }
        if( $transportmax < $transportmin ){
            // alert('Transport max price should be greater than Transport min price.');
            return false;
        }
		$Fobmin = 0;
		$Fobmax = 0;
		$totalMin = 0;
		$exchangeRatemin = 0;
		$totalMax = 0;
        $exchangeRatemax = 0;

        if( $ricemin != 0 && $ricemax != 0 && $transportmin != '' && $transportmax != '' && $category != '' && $transportmin != ''&& $transportmax != '' && $dollarrate != '' && $percentageValue != '' && $charges != ''){
           
            $totalMin = (float)((float)($ricemin)+(float)($category)+(float)($transportmin)+(float)($charges));
            $totalMax = (float)((float)($ricemax)+(float)($category)+(float)($transportmax)+(float)($charges));
            $exchangeRatemin = (float)(((float)($totalMin) / $dollarrate));
            $exchangeRatemax = (float)(((float)($totalMax)/ $dollarrate));
            $Fobmin = (((float)((($exchangeRatemin*$percentageValue)/100 )) + (float)((float)($exchangeRatemin))));
            $Fobmax = (((float)((($exchangeRatemax*$percentageValue)/100 )) + (float)((float)($exchangeRatemax))));

            $total = (($totalMin).' - '.($totalMax));
            $exchangeRate = (($exchangeRatemin).' - '.($exchangeRatemax));
            $fob = (($Fobmin).' - '.($Fobmax));
        }
			

        if( $ricemin != 0 && $ricemax == 0 && $transportmin != '' && $transportmax != '' && $category != '' && $transportmin != ''&& $transportmax != '' && $dollarrate != '' && $percentageValue != ''){
           
            $totalMin = (float)((float)($ricemin)+(float)($category)+(float)($transportmin)+(float)($charges));
            
            $exchangeRatemin = (float)(((float)($ricemin)+(float)($category)+(float)($transportmin) ) / $dollarrate);
            $Fobmin = (((float)((($exchangeRatemin*$percentageValue)/100 )) + (float)((float)($exchangeRatemin))));
            $Fobmax = (((float)((($exchangeRatemax*$percentageValue)/100 )) + (float)((float)($exchangeRatemax))));

            $total = (($totalMin).' - '.($totalMax));
			$exchangeRate = (($exchangeRatemin).' - '.($exchangeRatemax));
			$fob = (($Fobmin).' - '.($Fobmax));
        }
		
        if( $ricemin == 0 && $ricemax != 0 && $transportmin != '' && $transportmax != '' && $category != '' && $transportmin != ''&& $transportmax != '' && $dollarrate != '' && $percentageValue != ''){
           
            
            $totalMax = (float)((float)($ricemax)+(float)($category)+(float)($transportmax)+(float)($charges));
            
            $exchangeRatemax = (float)(((float)($ricemax)+(float)($category)+(float)($transportmax) ) / $dollarrate);
            $Fobmin = (((float)((($exchangeRatemin*$percentageValue)/100 )) + (float)((float)($exchangeRatemin))));
            $Fobmax = (((float)((($exchangeRatemax*$percentageValue)/100 )) + (float)((float)($exchangeRatemax))));

            $total = (($totalMin).' - '.($totalMax));
            $exchangeRate = (($exchangeRatemin).' - '.($exchangeRatemax));
            $fob = (($Fobmin).' - '.($Fobmax));
        }
        
        

        $totalMin 			= $totalMin;
        $totalMax 			= $totalMax;
        $exchangeRatemin 	= ( round($exchangeRatemin , 2) );
        $exchangeRatemax 	= ( round($exchangeRatemax , 2) );
        $Fobmin 			= ( round($Fobmin) );
        $Fobmax 			= ( round($Fobmax) );
        
        $qualityMaster = QualityMaster::where('id' , $riceName)->first();        
        if( $qualityMaster->quality_type_status == 1 ){
	        $applied_for = 0;
        }else{
	        $applied_for = 1;
        }

		$fiftyKGbagPMT = USD_defaultmaster::where([ 'applied_for' => $applied_for , 'bag_size' => '50kg' ])->first();

		$fiftyKGbagPMTPrice = $fiftyKGbagPMT->PMT_USD;

        $selectedAppliedFor = USD_defaultmaster::where('applied_for' ,$applied_for)->get();

        if($selectedAppliedFor->count() > 0){
        	foreach($selectedAppliedFor as $k => $v){
        		$bag_size = $v->bag_size;
        		$bag_id = $v->id;
        		$bag_pmt = $v->PMT_USD;

        		USD_prices::create([
		        	'rice' => $riceName,
		        	'ricemin' => $ricemin,
		        	'ricemax' => $ricemax,
		        	'transportmin' => $transportmin,
		        	'transportmax' => $transportmax,
		        	'category' => $category,
		        	'charges' => $charges,
		        	'dollarrate' => $dollarrate,
		        	'percentageValue' => $percentageValue,
		        	'totalMin' => $totalMin,
		        	'totalMax' => $totalMax,
		        	'exchangeRatemin' => $exchangeRatemin,
		        	'exchangeRatemax' => $exchangeRatemax,
		        	'fobmin' => round(((int)$Fobmin - (int)$fiftyKGbagPMTPrice) + (int)$bag_pmt),
		        	'fobmax' => round(((int)$Fobmax - (int)$fiftyKGbagPMTPrice) + (int)$bag_pmt),
		        	'status' => 1,
		        	'usd_defaultMaster_id' => $bag_id,
		        	'color_status' => $request->color_status
		        ]);
        	}
        }



        return back();
	}
	public function updateCalculator(Request $request)
	{
		$defaultData = USD_prices::where(['id' => $request->usdPriceId])->first();
		$riceName 		= $request->riceName;
		$ricemin 		= $request->ricemin;
		$ricemax 		= $request->ricemax;
		$transportmin 	= $request->portmin;
		$transportmax 	= $request->portmax;
		$category 		= $request->bag;
		$charges 		= $request->charges;
		$dollarrate 	= $request->dollar;
		$percentageValue= $request->percentage;
		$colorStatus= $request->color_status;

		if($ricemin == ''){
            $ricemin = 0;
        }
        if($ricemax == ''){
            $ricemax = 0;
        }
        if($transportmin == ''){
            $transportmin = 0;
        }
        if($transportmax == ''){
            $transportmax = 0;
        }
        if( $ricemax < $ricemin ){
            // alert('Rice max price should be greater than Rice min price.');
            return false;
        }
        if( $transportmax < $transportmin ){
            // alert('Transport max price should be greater than Transport min price.');
            return false;
        }

		$totalMin = '';
		$totalMax = '';
		$exchangeRatemin = '';
		$exchangeRatemax = '';
		$Fobmin = '';
		$Fobmax = '';
		$total = '';
		$exchangeRate = '';
		$fob = '';

        if( $ricemin != 0 && $ricemax != 0 && $transportmin != '' && $transportmax != '' && $category != '' && $transportmin != ''&& $transportmax != '' && $dollarrate != '' && $percentageValue != '' && $charges != ''){
           
            $totalMin = (float)((float)($ricemin)+(float)($category)+(float)($transportmin)+(float)($charges));
            $totalMax = (float)((float)($ricemax)+(float)($category)+(float)($transportmax)+(float)($charges));
            $exchangeRatemin = (float)(((float)($totalMin) / $dollarrate));
            $exchangeRatemax = (float)(((float)($totalMax)/ $dollarrate));
            $Fobmin = (((float)((($exchangeRatemin*$percentageValue)/100 )) + (float)((float)($exchangeRatemin))));
            $Fobmax = (((float)((($exchangeRatemax*$percentageValue)/100 )) + (float)((float)($exchangeRatemax))));

            $total = (($totalMin).' - '.($totalMax));
            $exchangeRate = (($exchangeRatemin).' - '.($exchangeRatemax));
            $fob = (($Fobmin).' - '.($Fobmax));
        }

        if( $ricemin != 0 && $ricemax == 0 && $transportmin != '' && $transportmax != '' && $category != '' && $transportmin != ''&& $transportmax != '' && $dollarrate != '' && $percentageValue != ''){
           
            $totalMin = (float)((float)($ricemin)+(float)($category)+(float)($transportmin)+(float)($charges));
            $totalMax = 0;
            $exchangeRatemin = (float)(((float)($ricemin)+(float)($category)+(float)($transportmin) ) / $dollarrate);
            $exchangeRatemax = 0;
            $Fobmin = (((float)((($exchangeRatemin*$percentageValue)/100 )) + (float)((float)($exchangeRatemin))));
            $Fobmax = (((float)((($exchangeRatemax*$percentageValue)/100 )) + (float)((float)($exchangeRatemax))));

            $total = (($totalMin).' - '.($totalMax));
			$exchangeRate = (($exchangeRatemin).' - '.($exchangeRatemax));
			$fob = (($Fobmin).' - '.($Fobmax));
        }

        if( $ricemin == 0 && $ricemax != 0 && $transportmin != '' && $transportmax != '' && $category != '' && $transportmin != ''&& $transportmax != '' && $dollarrate != '' && $percentageValue != ''){
           
            $totalMin = 0;
            $totalMax = (float)((float)($ricemax)+(float)($category)+(float)($transportmax)+(float)($charges));
            $exchangeRatemin = 0;
            $exchangeRatemax = (float)(((float)($ricemax)+(float)($category)+(float)($transportmax) ) / $dollarrate);
            $Fobmin = (((float)((($exchangeRatemin*$percentageValue)/100 )) + (float)((float)($exchangeRatemin))));
            $Fobmax = (((float)((($exchangeRatemax*$percentageValue)/100 )) + (float)((float)($exchangeRatemax))));

            $total = (($totalMin).' - '.($totalMax));
            $exchangeRate = (($exchangeRatemin).' - '.($exchangeRatemax));
            $fob = (($Fobmin).' - '.($Fobmax));
        }
        

        $totalMin 			= ( round($totalMin , 2) );
        $totalMax 			= ( round($totalMax , 2) );
        $exchangeRatemin 	= ( round($exchangeRatemin , 2) );
        $exchangeRatemax 	= ( round($exchangeRatemax , 2) );
        $Fobmin 			= ( round($Fobmin) );
        $Fobmax 			= ( round($Fobmax) );
        
        $qualityMaster = QualityMaster::where('id' , $riceName)->first();        
        if( $qualityMaster->quality_type_status == 1 ){
	        $applied_for = 0;
        }else{
	        $applied_for = 1;
        }

		$fiftyKGbagPMT = USD_defaultmaster::where([ 'applied_for' => $applied_for , 'bag_size' => '50kg' ])->first();

		$fiftyKGbagPMTPrice = $fiftyKGbagPMT->PMT_USD;

        $selectedAppliedFor = USD_defaultmaster::where('id' , $defaultData->usd_defaultMaster_id)->first();
        // dd($selectedAppliedFor);
        USD_prices::where(['id' => $request->usdPriceId])->update([
        	'rice' => $riceName,
        	'ricemin' => $ricemin,
        	'ricemax' => $ricemax,
        	'transportmin' => $transportmin,
        	'transportmax' => $transportmax,
        	'category' => $category,
        	'charges' => $charges,
        	'dollarrate' => $dollarrate,
        	'percentageValue' => $percentageValue,
        	'totalMin' => $totalMin,
        	'totalMax' => $totalMax,
        	'exchangeRatemin' => $exchangeRatemin,
        	'exchangeRatemax' => $exchangeRatemax,
        	'fobmin' => round(((int)$Fobmin - (int)$fiftyKGbagPMTPrice) + (int)$selectedAppliedFor->PMT_USD),
        	'fobmax' => round(((int)$Fobmax - (int)$fiftyKGbagPMTPrice) + (int)$selectedAppliedFor->PMT_USD),
        	'status' => 1,
        	'usd_defaultMaster_id' => $defaultData->usd_defaultMaster_id,
        	'color_status' => $colorStatus
        ]);
        // if($selectedAppliedFor->count() > 0){
        // 	foreach($selectedAppliedFor as $k => $v){
        // 		$bag_size = $v->bag_size;
        // 		$bag_id = $v->id;
        // 		$bag_pmt = $v->PMT_USD;
        		
        // 		USD_prices::where(['id' => $request->usdPriceId])->update([
		//         	'rice' => $riceName,
		//         	'ricemin' => $ricemin,
		//         	'ricemax' => $ricemax,
		//         	'transportmin' => $transportmin,
		//         	'transportmax' => $transportmax,
		//         	'category' => $category,
		//         	'charges' => $charges,
		//         	'dollarrate' => $dollarrate,
		//         	'percentageValue' => $percentageValue,
		//         	'totalMin' => $totalMin,
		//         	'totalMax' => $totalMax,
		//         	'exchangeRatemin' => $exchangeRatemin,
		//         	'exchangeRatemax' => $exchangeRatemax,
		//         	'fobmin' => round(((int)$Fobmin - (int)$fiftyKGbagPMTPrice) + (int)$bag_pmt),
		//         	'fobmax' => round(((int)$Fobmax - (int)$fiftyKGbagPMTPrice) + (int)$bag_pmt),
		//         	'status' => 1,
		//         	'usd_defaultMaster_id' => $bag_id,
		//         	'color_status' => $colorStatus
		//         ]);
        // 	}
        // }



        return back();
	}
	public function deleteRiceQualityUSD($id)
	{
		$usdPrideChangeStatus = USD_prices::where(['id' => $id])->update(['status' => 0]);
		return back();

	}
	public function updateToTodaysCalculation($id)
	{
		$usdprice = USD_prices::where('id' , $id)->get();
		if($usdprice->count() > 0){
			$usdpriceRice = $usdprice[0]->rice;
			$usdPriceCreated_at = $usdprice[0]->created_at;

			$formatCarbon = Carbon::parse($usdPriceCreated_at)->format('Y-m-d');

			$formatedCreatedAt = Carbon::parse($usdPriceCreated_at)->format('Y-m-d');

			$insertedData = USD_prices::select('rice','ricemin','ricemax','transportmin','transportmax','category','charges','dollarrate','percentageValue','totalMin','totalMax','exchangeRatemin','exchangeRatemax','fobmin','fobmax','usd_defaultMaster_id','status','user_id','color_status')->where('rice' , $usdpriceRice)->whereDate('created_at' , $formatedCreatedAt)->get()->toArray();
			foreach($insertedData as $k => $v){
				USD_prices::create($v);
			}
			return back();
		}else{
			dd('Something went wrong');
		}

	}
	public function listSellQueries()
	{
		$sellerQueries = SellQueriesINR::with(['RiceFormMilestone3','RiceQualityRiceNames','UserDetail','RicePacking','riceGrade' => function($query){
			return $query->with('getWandType')->get();
		}])->orderBy('id', 'DESC')->get();		

		return View('sellINR.index' , compact('sellerQueries'));

	}
	public function closeSellQueries($sellQueryId)
	{
		SellQueriesINR::where('id' , $sellQueryId)->update(['status' => 0]);
		Session::flash('message' , 'Sell query closed successfully.');

		return back();
	}
	public function moveToTradeSellQueries($sellQueryId)
	{
		SellQueriesINR::where('id' , $sellQueryId)->update(['status' => 2]);

		$userBuyer = SellQueriesINR::where('id' , $sellQueryId)->first();
		$userId = $userBuyer->created_by;
		$userDetail = User::where('id' , $userId)->first();

		$data = [];
		$mailTo = $userDetail->email;
        $mailMessage = '';
        $subject = 'Trade with SNTC';
        $mailFrom = 'info@sntcgroup.com';
        $mailFromName = 'SNTC Team - India';

		$respose = Mail::send('mail.TradeSellQueryToUser', $data, function($message) use ($mailTo, $mailMessage, $subject,$mailFrom,$mailFromName) {
            $message->to($mailTo, $mailMessage)->subject($subject);
            $message->from($mailFrom,$mailFromName);
        });

		Session::flash('message' , 'Sell query moved to trade successfully.');
		return back();
	}

	public function listBuyQueries()
	{
		$buyQueries = BuyQueriesINR::with(['RiceFormMilestone3','RiceQualityRiceNames','UserDetail','RicePacking','riceGrade' => function($query){
			return $query->with('getWandType')->get();
		}])->orderBy('id' ,'DESC')->get();

		return View('buyINR.index' , compact('buyQueries'));

	}
	public function closeBuyQueries($buyQueryId)
	{
		BuyQueriesINR::where('id' , $buyQueryId)->update(['status' => 0]);
		Session::flash('message' , 'Buy query closed successfully.');

		return back();
	}
	public function moveToTradeBuyQueries($buyQueryId)
	{

		$userBuyer = BuyQueriesINR::where('id' , $buyQueryId)->first();
		$userId = $userBuyer->created_by;
		$userDetail = User::where('id' , $userId)->first();
		BuyQueriesINR::where('id' , $buyQueryId)->update(['status' => 2]);

		$data = [];
		$mailTo = $userDetail->email;
        $mailMessage = '';
        $subject = 'Trade with SNTC';
        $mailFrom = 'info@sntcgroup.com';
        $mailFromName = 'SNTC Team - India';

		$respose = Mail::send('mail.TradeQueryToUser', $data, function($message) use ($mailTo, $mailMessage, $subject,$mailFrom,$mailFromName) {
            $message->to($mailTo, $mailMessage)->subject($subject);
            $message->from($mailFrom,$mailFromName);
        });
		Session::flash('message' , 'Buy query moved to trade successfully.');

		return back();
	}
}
