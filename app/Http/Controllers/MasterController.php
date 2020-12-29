<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RiceForm;
use App\RiceName;
use App\RiceType;
use App\Port;
use App\LivePrice;
use Session;

class MasterController extends Controller
{
	public function index(){
		return view('master.create');	
	}

	public function listRiceType(){
		$riceForm = RiceForm::get();
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

		RiceName::create(['name' => $request->name , 'type' => $request->riceType ]);
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
		LivePrice::create(['name' => 0,'form' => 0 , 'state' => $request->name , 'up_down' => 'up']);
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

		Port::create([ 'state' => $request->state,'route' => '0','price' => 0]);
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
		return back();
	}

	public function createRoute(Request $request)
	{
		$request->validate([
			'route' => 'required',
		]);

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
}