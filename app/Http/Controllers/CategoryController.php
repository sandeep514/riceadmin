<?php

namespace App\Http\Controllers;

use App\Category;
use App\CategoryRoleMap;
use App\Http\Requests\CategoryRequest;
use App\TradeCategoryMap;
use App\WebAccess;
use App\WebPlanModel;
use Session;

class CategoryController extends Controller
{
    public function index()
    {
        $records = Category::orderByRaw('COALESCE(`order`, 999999)')->orderBy('category')->get();

        return view('category-masters.index', compact('records'));
    }

    public function create()
    {
        return view('category-masters.create');
    }

    public function save(CategoryRequest $request)
    {
        Category::create($request->only([
            'category',
            'description',
            'order',
            'status',
        ]));

        Session::flash('success', 'Success|Category saved successfully!');

        return redirect()->route('category-masters');
    }

    public function edit($id)
    {
        $model = Category::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        return view('category-masters.edit', compact('model'));
    }

    public function update(CategoryRequest $request, $id)
    {
        $model = Category::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->update($request->only([
            'category',
            'description',
            'order',
            'status',
        ]));

        Session::flash('success', 'Success|Category updated successfully!');

        return redirect()->route('category-masters');
    }

    public function delete($id)
    {
        $model = Category::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        if ($this->isCategoryInUse((int) $id)) {
            Session::flash('error', 'Error|Cannot delete. This category is mapped to roles, trades, plans or web access.');

            return back();
        }

        $model->delete();
        Session::flash('success', 'Success|Category deleted successfully!');

        return back();
    }

    public function changeStatus($id)
    {
        $model = Category::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->status = (int) $model->status === 1 ? 0 : 1;
        $model->save();

        $msg = (int) $model->status === 1
            ? 'Success|Category marked as active.'
            : 'Success|Category marked as inactive.';
        Session::flash('success', $msg);

        return back();
    }

    private function isCategoryInUse(int $id): bool
    {
        return CategoryRoleMap::where('category', $id)->exists()
            || TradeCategoryMap::where('category_id', $id)->exists()
            || WebPlanModel::where('category_id', $id)->exists()
            || WebAccess::where('category_id', $id)->exists();
    }
}
