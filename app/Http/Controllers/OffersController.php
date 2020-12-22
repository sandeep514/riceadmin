<?php

namespace App\Http\Controllers;

use App\DataTables\OffersDataTable;
use App\Http\Requests\OfferFormRequest;
use App\Offer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;

class OffersController extends Controller
{
    public function index(OffersDataTable $dataTable){
        return $dataTable->render('offers.index');
    }

    public function create(){
        return view('offers.create');
    }

    public function save(OfferFormRequest $request){
        $offerModel = new Offer;
        $offerModel->fill($request->except(['date']));
        $offerModel->date = Carbon::parse($request->date)->format('Y-m-d h:i:s');
        $offerModel->user_id = Auth::user()->id;
        $offerModel->save();
        Session::flash('success','Success|Record saved successfully!');
        return back();
    }

    public function edit($id){
        $offerModel = Offer::find($id);
        return view('offers.edit',['model'=>$offerModel]);
    }

    public function update(OfferFormRequest $request, $id){
        $offerModel = Offer::find($id);
        $offerModel->fill($request->except(['date']));
        $offerModel->date = Carbon::parse($request->date)->format('Y-m-d h:i:s');
        $offerModel->user_id = Auth::user()->id;
        $offerModel->save();
        Session::flash('success','Success|Record saved successfully!');
        return redirect()->route('offers');
    }

    public function delete($id){
        $offerModel = Offer::find($id);
        $offerModel->delete();
        Session::flash('success','Success|Record deleted successfully!');
        return back();
    }
}
