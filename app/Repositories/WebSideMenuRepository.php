<?php

namespace App\Repositories;

use App\WebSideMenu;

class WebSideMenuRepository
{
    /**
     * Generate slug from title: lowercase and replace spaces with underscores
     */
    private static function generateSlug($title, $excludeId = null){
        // Convert to lowercase and replace spaces with underscores
        $slug = strtolower(str_replace(' ', '_', trim($title)));
        
        // Remove special characters, keep only alphanumeric and underscores
        $slug = preg_replace('/[^a-z0-9_]/', '', $slug);
        
        // Remove multiple consecutive underscores
        $slug = preg_replace('/_+/', '_', $slug);
        
        // Remove leading/trailing underscores
        $slug = trim($slug, '_');
        
        // Ensure slug is unique
        $originalSlug = $slug;
        $counter = 1;
        $query = WebSideMenu::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        while ($query->exists()) {
            $slug = $originalSlug . '_' . $counter;
            $query = WebSideMenu::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            $counter++;
        }
        
        return $slug;
    }

    public static function saveWebSideMenu($request){
        $lastOrder = WebSideMenu::max('sort_order') ?? 0;
        
        // Generate slug from title: lowercase and replace spaces with underscores
        $slug = self::generateSlug($request->title);
        
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
        
        // ✅ Slug should not change on update - keep existing slug
        return $menu->update([
            'title' => $request->title,
            'sub_title' => $request->sub_title,
            // Slug is not updated - keeps original value
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

