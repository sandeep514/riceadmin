<?php

namespace App\Http\Controllers;

use App\Services\SitePolicyPdfService;
use App\SitePolicy;
use App\Support\MasterOrderUpdater;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Session;

class SitePolicyController extends Controller
{
    public function index()
    {
        $records = SitePolicy::query()
            ->orderByRaw('order_no IS NULL, order_no ASC')
            ->orderBy('id')
            ->get();

        return view('sitePolicies.index', compact('records'));
    }

    public function create()
    {
        $predefined = SitePolicy::predefinedTypes();
        $usedSlugs = SitePolicy::query()->pluck('slug')->all();

        return view('sitePolicies.create', compact('predefined', 'usedSlugs'));
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required|string|max:100|unique:site_policies,slug|regex:/^[a-z0-9_\-]+$/',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:0,1',
        ], [
            'slug.regex' => 'Slug may only contain lowercase letters, numbers, dashes and underscores.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $slug = strtolower(trim((string) $request->input('slug')));
        $slug = preg_replace('/[^a-z0-9_\-]+/', '', str_replace(' ', '_', $slug));
        if ($slug === '') {
            return back()->withErrors(['slug' => 'Invalid slug.'])->withInput();
        }

        $predefined = SitePolicy::predefinedTypes();
        $title = trim((string) $request->input('title'));
        if ($title === '' && isset($predefined[$slug])) {
            $title = $predefined[$slug];
        }

        $policy = SitePolicy::create([
            'slug' => $slug,
            'title' => $title,
            'content' => $request->input('content'),
            'status' => (int) $request->input('status'),
            'order_no' => MasterOrderUpdater::nextOrder(SitePolicy::class),
        ]);

        app(SitePolicyPdfService::class)->generateAndStore($policy);

        Session::flash('success', 'Success|Policy saved and PDF generated.');

        return redirect()->route('site.policies');
    }

    public function edit($id)
    {
        $model = SitePolicy::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $predefined = SitePolicy::predefinedTypes();

        return view('sitePolicies.edit', compact('model', 'predefined'));
    }

    public function update(Request $request, $id)
    {
        $model = SitePolicy::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $model->update([
            'title' => trim((string) $request->input('title')),
            'content' => $request->input('content'),
            'status' => (int) $request->input('status'),
        ]);

        app(SitePolicyPdfService::class)->generateAndStore($model->fresh());

        Session::flash('success', 'Success|Policy updated and PDF regenerated.');

        return redirect()->route('site.policies');
    }

    public function delete($id)
    {
        $model = SitePolicy::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        if ($model->pdf_path) {
            $path = public_path(ltrim($model->pdf_path, '/'));
            if (is_file($path)) {
                File::delete($path);
            }
        }

        $model->delete();
        Session::flash('success', 'Success|Policy deleted successfully!');

        return back();
    }

    public function changeStatus($id)
    {
        $model = SitePolicy::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->status = (int) $model->status === SitePolicy::STATUS_ACTIVE
            ? SitePolicy::STATUS_INACTIVE
            : SitePolicy::STATUS_ACTIVE;
        $model->save();

        Session::flash('success', 'Success|Status updated successfully!');

        return back();
    }

    public function regeneratePdf($id)
    {
        $model = SitePolicy::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $path = app(SitePolicyPdfService::class)->generateAndStore($model);
        if ($path) {
            Session::flash('success', 'Success|PDF regenerated successfully.');
        } else {
            Session::flash('error', 'Error|PDF generation failed. Check logs.');
        }

        return back();
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:site_policies,id',
            'order_no' => 'required|integer|min:1',
        ]);

        MasterOrderUpdater::swap(SitePolicy::class, (int) $request->id, (int) $request->order_no);
        Session::flash('success', 'Success|Policy order updated successfully.');

        return back();
    }
}
