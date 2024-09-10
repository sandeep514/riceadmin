<?php

namespace App\Http\Controllers;

use App\DataTables\SamplesDataTable;
use App\Http\Requests\SampleRequest;

use App\Services\SampleService;
use Illuminate\Http\Request;
use Session;

use Carbon\Carbon;
use App\NewsRunner;

class NewsRunnerController extends Controller
{
    public function index()
    {
        $news = NewsRunner::limit(10)->orderBy('id', 'desc')->get();
        return View('newsrunner.index' , compact('news'));
    }

    public function create(Request $request)
    {   
        $data = [];
        foreach ($request->type as $key => $value) {
            $data[] = [ 'type' => $value , 'title' => $request->title];
        }
        Session::flash('message' , 'News updated successfully');
        NewsRunner::insert($data);
        return back();
    }
    public function updateStatus($newsId , $status)
    {
        NewsRunner::whereId($newsId)->update(['status'=> $status]);
        Session::flash('message' , 'Status updated successfully');
        return back();

    }
}