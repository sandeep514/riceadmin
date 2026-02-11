<?php

namespace App\Repositories;

use App\WebSideMenu;
use Illuminate\Support\Str;

class WebSideMenuRepository
{
    public static function saveWebSideMenu($request){
        $lastOrder = WebSideMenu::max('sort_order') ?? 0;
        
        // Generate slug from title
        $slug = Str::slug($request->title);
        
        // Ensure slug is unique
        $originalSlug = $slug;
        $counter = 1;
        while (WebSideMenu::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return WebSideMenu::create([
            'title' => $request->title,
            'sub_title' => $request->sub_title,
            'slug' => $slug,
            'create_url' => $request->create_url,
            'read_url' => $request->read_url,
            'update_url' => $request->update_url,
            'delete_url' => $request->delete_url,
            'status' => $request->status ?? 1,
            'sort_order' => $request->sort_order ?? ($lastOrder + 1),
        ]);
    }

    public static function updateWebSideMenu($request, $id){
        $menu = WebSideMenu::find($id);
        if($menu == null){
            return false;
        }
        
        return $menu->update([
            'title' => $request->title,
            'sub_title' => $request->sub_title,
            'create_url' => $request->create_url,
            'read_url' => $request->read_url,
            'update_url' => $request->update_url,
            'delete_url' => $request->delete_url,
            'status' => $request->status ?? 1,
            'sort_order' => $request->sort_order ?? $menu->sort_order,
        ]);
    }

    public static function deleteWebSideMenu($id){
        $menu = WebSideMenu::find($id);
        if($menu == null){
            return false;
        }
        return $menu->delete();
    }
}

