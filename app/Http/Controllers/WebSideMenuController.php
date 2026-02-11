<?php

namespace App\Http\Controllers;

use App\Http\Requests\WebSideMenuRequest;
use App\WebSideMenu;
use App\Services\WebSideMenuService;
use Illuminate\Http\Request;
use Session;
use Yajra\DataTables\Facades\DataTables;

class WebSideMenuController extends Controller
{
    public function index()
    {
        return view('web-side-menu.index');
    }

    public function getData()
    {
        $menus = WebSideMenu::orderBy('sort_order', 'asc')->orderBy('id', 'asc');
        
        return DataTables::eloquent($menus)
            ->addColumn('action', function($menu){
                return view('web-side-menu._actions',['id'=>$menu->id])->render();
            })
            ->editColumn('status', function($menu){
                return $menu->status == 1 ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>';
            })
            ->editColumn('sort_order', function($menu){
                return '<input type="number" class="form-control sort-order-input" data-id="'.$menu->id.'" value="'.$menu->sort_order.'" style="width: 80px;">';
            })
            ->rawColumns(['action', 'status', 'sort_order'])
            ->make(true);
    }

    public function create()
    {
        return view('web-side-menu.create');
    }

    public function save(WebSideMenuRequest $request)
    {
        WebSideMenuService::saveWebSideMenu($request);
        Session::flash('success','Success|Record Saved Successfully!');
        return redirect()->route('web-side-menu');
    }

    public function show($id)
    {
        $menu = WebSideMenu::find($id);
        if($menu == null){
            Session::flash('error','Error|No record found!');
            return back();
        }
        return view('web-side-menu.show',['model'=>$menu]);
    }

    public function edit($id)
    {
        $menu = WebSideMenu::find($id);
        if($menu == null){
            Session::flash('error','Error|No record found!');
            return back();
        }
        return view('web-side-menu.edit',['model'=>$menu]);
    }

    public function update(WebSideMenuRequest $request, $id)
    {
        WebSideMenuService::updateWebSideMenu($request,$id);
        Session::flash('success','Success|Record Updated Successfully!');
        return redirect()->route('web-side-menu');
    }

    public function delete($id)
    {
        $deleteMenu = WebSideMenuService::deleteWebSideMenu($id);
        if($deleteMenu == false){
            Session::flash('error','Error|Unable to delete record!');
            return back();
        }else{
            Session::flash('success','Success|Record deleted successfully!');
            return back();
        }
    }

    public function updateSortOrder(Request $request)
    {
        $data = array_combine($request->id, $request->sort_order);
        foreach($data as $id => $order){
            WebSideMenu::where('id', $id)->update(['sort_order' => $order]);
        }
        Session::flash('success','Success|Sort order updated successfully!');
        return back();
    }
}

