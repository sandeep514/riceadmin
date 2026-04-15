<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostedJobRequest;
use App\PostedJob;
use Session;

class PostedJobController extends Controller
{
    public function index()
    {
        $jobs = PostedJob::orderBy('id', 'DESC')->get();

        return view('posted-jobs.index', compact('jobs'));
    }

    public function create()
    {
        return view('posted-jobs.create');
    }

    public function save(PostedJobRequest $request)
    {
        $job = new PostedJob;
        $job->fill($request->only([
            'title',
            'description',
            'job_role',
            'location',
            'employment_type',
            'last_date_apply',
            'number_of_positions',
        ]));
        $job->status = PostedJob::STATUS_ACTIVE;
        $job->save();

        Session::flash('success', 'Success|Job posted saved successfully!');

        return redirect()->route('post-a-job');
    }

    public function edit($id)
    {
        $job = PostedJob::findOrFail($id);

        return view('posted-jobs.edit', compact('job'));
    }

    public function update(PostedJobRequest $request, $id)
    {
        $job = PostedJob::findOrFail($id);
        $job->fill($request->only([
            'title',
            'description',
            'job_role',
            'location',
            'employment_type',
            'last_date_apply',
            'number_of_positions',
        ]));
        $job->save();

        Session::flash('success', 'Success|Job posted updated successfully!');

        return redirect()->route('post-a-job');
    }

    public function delete($id)
    {
        PostedJob::where('id', $id)->delete();
        Session::flash('success', 'Success|Job posting deleted successfully!');

        return back();
    }

    /**
     * Toggle visibility: status 1 = Active (public), 0 = Deactive (hidden from public API).
     */
    public function changeStatus($id, $status)
    {
        $status = (int) $status;
        if (! in_array($status, [PostedJob::STATUS_ACTIVE, PostedJob::STATUS_INACTIVE], true)) {
            abort(404);
        }

        $job = PostedJob::findOrFail($id);
        $job->status = $status;
        $job->save();

        $msg = $status === PostedJob::STATUS_ACTIVE
            ? 'Success|Job marked as active.'
            : 'Success|Job marked as deactive.';

        Session::flash('success', $msg);

        return back();
    }
}
