<?php

namespace App\Http\Controllers;

use App\Http\Requests\WebAccessRequest;
use App\WebAccess;
use App\Role;
use App\Category;
use App\WebPlanModel;
use App\WebSideMenu;
use App\CategoryRoleMap;
use App\Services\WebAccessService;
use Illuminate\Http\Request;
use Session;
use Yajra\DataTables\Facades\DataTables;

class WebAccessController extends Controller
{
    public function index()
    {
        return view('web-access.index');
    }

    public function getData()
    {
        // Get all access records grouped by role_id, category_id, plan_id
        $allAccess = WebAccess::with(['role', 'category', 'plan', 'webSideMenu'])->get();
        
        // Group by role_id, category_id, plan_id
        $grouped = $allAccess->groupBy(function($item) {
            return $item->role_id . '_' . ($item->category_id ?? 'null') . '_' . ($item->plan_id ?? 'null');
        });
        
        // Build the data array for DataTables
        $data = [];
        foreach($grouped as $key => $items) {
            $firstItem = $items->first();
            $data[] = [
                'id' => $firstItem->id,
                'role_id' => $firstItem->role ? $firstItem->role->role_name : '-',
                'category_id' => $firstItem->category ? $firstItem->category->category : '-',
                'plan_id' => $firstItem->plan ? $firstItem->plan->title : '-',
                'menu_items_count' => '<span class="badge bg-blue">' . $items->count() . ' Menu Items</span>',
                'status' => $firstItem->status == 1 ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>',
                'action' => view('web-access._actions',['id'=>$firstItem->id])->render(),
            ];
        }
        
        // Sort by id descending
        usort($data, function($a, $b) {
            return $b['id'] - $a['id'];
        });
        
        return DataTables::of(collect($data))
            ->rawColumns(['action', 'menu_items_count', 'status'])
            ->make(true);
    }

    public function create()
    {
        $roles = Role::where('type', 'web')->pluck('role_name', 'id')->toArray();
        $categories = []; // Empty - will be loaded via AJAX based on selected role
        $plans = WebPlanModel::where('status', 1)->pluck('title', 'id')->toArray();
        $menus = WebSideMenu::where('status', 1)->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
        $accesses = collect(); // Empty collection for create
        
        return view('web-access.create', compact('roles', 'categories', 'plans', 'menus', 'accesses'));
    }

    public function save(WebAccessRequest $request)
    {
        WebAccessService::saveWebAccess($request);
        Session::flash('success','Success|Record Saved Successfully!');
        return redirect()->route('web-access');
    }

    public function edit($id)
    {
        $access = WebAccess::find($id);
        if($access == null){
            Session::flash('error','Error|No record found!');
            return back();
        }
        
        $roles = Role::where('type', 'web')->pluck('role_name', 'id')->toArray();
        // Get categories for the selected role
        $categoryMaps = CategoryRoleMap::where('role', $access->role_id)
            ->where('status', 1)
            ->with('category_rel')
            ->get();
        $categories = [];
        foreach($categoryMaps as $map) {
            if($map->category_rel) {
                $categories[$map->category_rel->id] = $map->category_rel->category;
            }
        }
        $plans = WebPlanModel::where('status', 1)->pluck('title', 'id')->toArray();
        $menus = WebSideMenu::where('status', 1)->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
        
        // Get all access records for this role/category/plan combination
        $query = WebAccess::where('role_id', $access->role_id);
        if ($access->category_id) {
            $query->where('category_id', $access->category_id);
        } else {
            $query->whereNull('category_id');
        }
        if ($access->plan_id) {
            $query->where('plan_id', $access->plan_id);
        } else {
            $query->whereNull('plan_id');
        }
        $accesses = $query->get();
        
        return view('web-access.edit', compact('access', 'roles', 'categories', 'plans', 'menus', 'accesses'));
    }

    public function update(WebAccessRequest $request, $id)
    {
        $access = WebAccess::find($id);
        if($access == null){
            Session::flash('error','Error|No record found!');
            return back();
        }
        
        WebAccessService::updateWebAccess($request, $access->role_id, $access->category_id, $access->plan_id);
        Session::flash('success','Success|Record Updated Successfully!');
        return redirect()->route('web-access');
    }

    public function delete($id)
    {
        $deleteAccess = WebAccessService::deleteWebAccess($id);
        if($deleteAccess == false){
            Session::flash('error','Error|Unable to delete record!');
            return back();
        }else{
            Session::flash('success','Success|Record deleted successfully!');
            return back();
        }
    }

    public function getCategoriesByRole(Request $request)
    {
        $roleId = $request->role_id;
        $categoryMaps = CategoryRoleMap::where('role', $roleId)
            ->where('status', 1)
            ->with('category_rel')
            ->get();
        
        $categories = [];
        foreach($categoryMaps as $map) {
            if($map->category_rel) {
                $categories[$map->category_rel->id] = $map->category_rel->category;
            }
        }
        
        return response()->json($categories);
    }

    public function getPlanByRoleCategory(Request $request)
    {
        $roleId     = $request->role_id;
        $categoryId = $request->category_id;

        // 1. Try exact match by role_id + category_id
        $plan = WebPlanModel::where('role_id', $roleId)
            ->where('category_id', $categoryId)
            ->where('status', 1)
            ->first(['id', 'title']);

        // 2. Fallback: match by auto-generated title pattern (roleName_categoryName)
        if (!$plan) {
            $role        = Role::find($roleId);
            $categoryMap = CategoryRoleMap::with('category_rel')
                ->where('role', $roleId)
                ->where('category', $categoryId)
                ->first();

            if ($role && $categoryMap && $categoryMap->category_rel) {
                $slug = strtolower(str_replace(' ', '_', $role->role_name))
                      . '_'
                      . strtolower(str_replace(' ', '_', $categoryMap->category_rel->category));

                $plan = WebPlanModel::where('title', $slug)
                    ->where('status', 1)
                    ->first(['id', 'title']);
            }
        }

        if ($plan) {
            return response()->json(['found' => true, 'id' => $plan->id, 'title' => $plan->title]);
        }

        return response()->json(['found' => false, 'id' => null, 'title' => null]);
    }

}

