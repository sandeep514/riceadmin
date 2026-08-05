<?php

namespace App\Http\Controllers;

use App\PaddySellQuery;
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

    public function view($id)
    {
        $query = PaddySellQuery::with(['paddyQuality', 'user'])->findOrFail($id);

        return view('paddySellQuery.view', compact('query'));
    }

    public function downloadImage($id)
    {
        $query = PaddySellQuery::findOrFail($id);

        if (! $query->image) {
            Session::flash('error', 'Error|No image found for this query.');

            return back();
        }

        $path = public_path('uploads/' . ltrim($query->image, '/'));

        if (! is_file($path)) {
            Session::flash('error', 'Error|Image file is missing on server.');

            return back();
        }

        return response()->download($path, basename($path));
    }

    public function close($id)
    {
        $query = PaddySellQuery::findOrFail($id);
        $query->update(['status' => 0]);

        Session::flash('success', 'Success|Paddy sell query closed successfully.');

        return back();
    }
}
