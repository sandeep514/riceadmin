<?php

namespace App\Http\Controllers;

use App\Category;
use App\CategoryRoleMap;
use App\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

class RoleCategoryMapController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::where('type', 'web')->orderBy('role_name')->get();
        $categories = Category::where('status', 1)->orderBy('order', 'asc')->orderBy('category', 'asc')->get();

        $selectedRoleId = $request->get('role_id');
        if (!$selectedRoleId && $roles->count() > 0) {
            $selectedRoleId = $roles->first()->id;
        }

        $selectedCategoryIds = [];
        if ($selectedRoleId) {
            $selectedCategoryIds = CategoryRoleMap::where('role', $selectedRoleId)
                ->where('status', 1)
                ->pluck('category')
                ->map(function ($id) {
                    return (int) $id;
                })
                ->toArray();
        }

        return view('role-category-map.index', compact(
            'roles',
            'categories',
            'selectedRoleId',
            'selectedCategoryIds'
        ));
    }

    public function save(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:category,id',
        ]);

        $roleId = (int) $request->role_id;
        $categories = collect($request->input('categories', []))
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values();

        DB::transaction(function () use ($roleId, $categories) {
            CategoryRoleMap::where('role', $roleId)->delete();

            foreach ($categories as $categoryId) {
                CategoryRoleMap::create([
                    'role' => $roleId,
                    'category' => $categoryId,
                    'status' => 1,
                ]);
            }
        });

        Session::flash('success', 'Success|Role category mapping updated successfully!');
        return redirect()->route('role-category-map.index', ['role_id' => $roleId]);
    }
}
