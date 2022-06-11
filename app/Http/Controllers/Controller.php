<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\BuyQuery;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function riceQualityMaster()
    {
        $buyQueries = BuyQuery::with(['getBids' => function($query){
            return $query->with(['seller'])->get();
        }])->get();
        return view('riceQueries.index' , compact('buyQueries'));
    }
}
