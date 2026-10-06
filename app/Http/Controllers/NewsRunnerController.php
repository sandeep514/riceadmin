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

    public function webEdit($newsId)
    {
        $editNews = WebNewsRunner::findOrFail($newsId);
        $news = WebNewsRunner::orderByDesc('news_date')
            ->orderByDesc('id')
            ->get();

        return View('webnewsrunner.index', compact('news', 'editNews'));
    }

    public function webUpdate(Request $request, $newsId)
    {
        $request->validate([
            'type' => 'required|array',
            'type.*' => 'in:usd,inr',
            'newsType' => 'required|in:recent,sntc',
            'news_date' => 'required|date',
            'title' => 'nullable|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:1,2',
        ]);

        $title = trim((string) $request->input('title', ''));
        $title = $title === '' ? null : $title;
        $newsDate = Carbon::parse($request->news_date)->format('Y-m-d');

        // Keep single row per record on edit (type checkbox acts as single-select here).
        // If multiple types checked, keep the first one to avoid duplicating rows.
        $type = is_array($request->type) ? reset($request->type) : $request->type;

        WebNewsRunner::whereId($newsId)->update([
            'type' => $type,
            'title' => $title,
            'description' => $request->description,
            'newsType' => $request->newsType,
            'news_date' => $newsDate,
            'status' => $request->status,
        ]);

        Session::flash('message', 'News updated successfully');
        return redirect()->route('web.master.news.runner');
    }

    public function edit($newsId)
    {
        $editNews = NewsRunner::findOrFail($newsId);
        $news = NewsRunner::limit(10)->orderBy('id', 'desc')->get();

        return View('newsrunner.index', compact('news', 'editNews'));
    }

    public function update(Request $request, $newsId)
    {
        $request->validate([
            'type' => 'required|in:usd,inr',
            'title' => 'required|string|max:255',
            'status' => 'required|in:1,2',
        ]);

        NewsRunner::whereId($newsId)->update([
            'type' => $request->type,
            'title' => $request->title,
            'status' => $request->status,
        ]);

        Session::flash('message', 'News updated successfully');
        return redirect()->route('master.news.runner');
    }
}