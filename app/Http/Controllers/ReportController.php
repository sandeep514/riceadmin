<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\LivePrice;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function livePrices(Request $request)
    {
        $from = $request->get('from');
        $to   = $request->get('to');

        $query = LivePrice::query()
            ->with([
                'name_rel:id,name',
                'form_rel:id,form_name'
            ])
            ->whereNotNull('name')
            ->whereNotNull('form');

        if ($from) {
            $fromDate = Carbon::parse($from)->startOfDay();
            $query->where('created_at', '>=', $fromDate);
        }
        if ($to) {
            $toDate = Carbon::parse($to)->endOfDay();
            $query->where('created_at', '<=', $toDate);
        }

        $rows = $query->orderBy('created_at', 'desc')->limit(5000)->get();

        return view('reports.live_prices', [
            'rows' => $rows,
            'from' => $from,
            'to'   => $to
        ]);
    }
}

