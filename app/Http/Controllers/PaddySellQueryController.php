<?php

namespace App\Http\Controllers;

use App\PaddySellQuery;
use Illuminate\Http\Request;
use Session;

class PaddySellQueryController extends Controller
{
    public function index()
    {
        $queries = PaddySellQuery::with(['paddyQuality', 'user'])
            ->orderByDesc('id')
            ->get();

        return view('paddySellQuery.index', compact('queries'));
    }

    public function close($id)
    {
        $query = PaddySellQuery::findOrFail($id);
        $query->update(['status' => 0]);

        Session::flash('success', 'Success|Paddy sell query closed successfully.');

        return back();
    }
}
