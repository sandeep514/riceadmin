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
                    'allowed_years' => $request->allowed_years ? array_values(array_map('intval', (array) $request->allowed_years)) : null,
                ]);
            }
        }
        
        return $saved;
    }

    public static function updateWebAccess($request, $roleId, $categoryId = null, $planId = null)
    {
        $menuPermissions = $request->menu_permissions ?? [];
        $allowedYears = $request->allowed_years ? array_values(array_map('intval', (array) $request->allowed_years)) : null;

        foreach ($menuPermissions as $menuId => $permissions) {
            $hasAnyPermission =
                isset($permissions['can_create']) ||
                isset($permissions['can_read']) ||
                isset($permissions['can_update']) ||
                isset($permissions['can_delete']);

            $recordQuery = WebAccess::where('role_id', $roleId)
                ->where('web_side_menu_id', $menuId);

            if ($categoryId) {
                $recordQuery->where('category_id', $categoryId);
            } else {
                $recordQuery->whereNull('category_id');
            }

            if ($planId) {
                $recordQuery->where('plan_id', $planId);
            } else {
                $recordQuery->whereNull('plan_id');
            }

            $existing = $recordQuery->first();

            if ($hasAnyPermission) {
                $payload = [
                    'role_id' => $roleId,
                    'category_id' => $categoryId,
                    'plan_id' => $planId,
                    'web_side_menu_id' => $menuId,
                    'can_create' => isset($permissions['can_create']) ? 1 : 0,
                    'can_read' => isset($permissions['can_read']) ? 1 : 0,
                    'can_update' => isset($permissions['can_update']) ? 1 : 0,
                    'can_delete' => isset($permissions['can_delete']) ? 1 : 0,
                    'status' => $request->status ?? 1,
                    'allowed_years' => $allowedYears,
                ];

                if ($existing) {
                    $existing->update($payload);
                } else {
                    WebAccess::create($payload);
                }
            } else {
                // No permissions checked for this menu; remove existing record if present
                if ($existing) {
                    $existing->delete();
                }
            }
        }

        // Also remove any existing records for menus that are no longer present in the request
        $submittedMenuIds = array_map('intval', array_keys($menuPermissions));
        $cleanupQuery = WebAccess::where('role_id', $roleId);
        if ($categoryId) {
            $cleanupQuery->where('category_id', $categoryId);
        } else {
            $cleanupQuery->whereNull('category_id');
        }
        if ($planId) {
            $cleanupQuery->where('plan_id', $planId);
        } else {
            $cleanupQuery->whereNull('plan_id');
        }
        if (!empty($submittedMenuIds)) {
            $cleanupQuery->whereNotIn('web_side_menu_id', $submittedMenuIds);
        }
        $cleanupQuery->delete();

        return true;
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
