<?php

namespace App\Http\Controllers;

use App\DataTables\SamplesDataTable;
use App\Http\Requests\SampleRequest;

use App\Services\SampleService;
use Illuminate\Http\Request;
use Session;

use Carbon\Carbon;
use App\NewsRunner;
use App\WebNewsRunner;

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

    public function webIndex()
    {
        $news = WebNewsRunner::orderByDesc('news_date')
            ->orderByDesc('id')
            ->get();

        return View('webnewsrunner.index' , compact('news'));
    }

    public function webCreate(Request $request)
    {   
        $request->validate([
            'type' => 'required|array',
            'type.*' => 'in:usd,inr',
            'newsType' => 'required|in:recent,sntc',
            'news_date' => 'required|date',
            'title' => 'nullable|string|max:255',
            'description' => 'required|string',
        ]);

        $title = trim((string) $request->input('title', ''));
        $title = $title === '' ? null : $title;
        $newsDate = Carbon::parse($request->news_date)->format('Y-m-d');

        $data = [];
        foreach ($request->type as $key => $value) {
            $data[] = [
                'type' => $value,
                'title' => $title,
                'description' => $request->description,
                'newsType' => $request->newsType,
                'news_date' => $newsDate,
            ];
        }
        Session::flash('message' , 'News updated successfully');
        WebNewsRunner::insert($data);
        return back();
    }

    public function webUpdateStatus($newsId , $status)
    {
        WebNewsRunner::whereId($newsId)->update(['status'=> $status]);
        Session::flash('message' , 'Status updated successfully');
        return back();
    }
}