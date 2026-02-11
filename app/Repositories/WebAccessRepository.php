<?php

namespace App\Repositories;

use App\WebAccess;

class WebAccessRepository
{
    public static function saveWebAccess($request)
    {
        $saved = [];
        $menuPermissions = $request->menu_permissions ?? [];
        
        foreach ($menuPermissions as $menuId => $permissions) {
            // Only create record if at least one permission is checked
            if (isset($permissions['can_create']) || isset($permissions['can_read']) || 
                isset($permissions['can_update']) || isset($permissions['can_delete'])) {
                
                $saved[] = WebAccess::create([
                    'role_id' => $request->role_id,
                    'category_id' => $request->category_id,
                    'plan_id' => $request->plan_id,
                    'web_side_menu_id' => $menuId,
                    'can_create' => isset($permissions['can_create']) ? 1 : 0,
                    'can_read' => isset($permissions['can_read']) ? 1 : 0,
                    'can_update' => isset($permissions['can_update']) ? 1 : 0,
                    'can_delete' => isset($permissions['can_delete']) ? 1 : 0,
                    'status' => $request->status ?? 1,
                ]);
            }
        }
        
        return $saved;
    }

    public static function updateWebAccess($request, $roleId, $categoryId = null, $planId = null)
    {
        // Delete all existing access records for this role/category/plan combination
        $query = WebAccess::where('role_id', $roleId);
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        } else {
            $query->whereNull('category_id');
        }
        if ($planId) {
            $query->where('plan_id', $planId);
        } else {
            $query->whereNull('plan_id');
        }
        $query->delete();
        
        // Create new records using the same logic as save
        return self::saveWebAccess($request);
    }

    public static function deleteWebAccess($id)
    {
        $access = WebAccess::find($id);
        if($access == null){
            return false;
        }
        
        // Delete all records for this role/category/plan combination
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
        
        return $query->delete();
    }
}

