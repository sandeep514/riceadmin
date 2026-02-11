<?php

namespace App\Services;

use App\Repositories\WebAccessRepository;

class WebAccessService
{
    public static function saveWebAccess($request)
    {
        return WebAccessRepository::saveWebAccess($request);
    }

    public static function updateWebAccess($request, $roleId, $categoryId = null, $planId = null)
    {
        return WebAccessRepository::updateWebAccess($request, $roleId, $categoryId, $planId);
    }

    public static function deleteWebAccess($id)
    {
        return WebAccessRepository::deleteWebAccess($id);
    }
}

