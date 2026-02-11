<?php

namespace App\Services;

use App\Repositories\WebSideMenuRepository;

class WebSideMenuService
{
    public static function saveWebSideMenu($request){
        return WebSideMenuRepository::saveWebSideMenu($request);
    }

    public static function updateWebSideMenu($request, $id){
        return WebSideMenuRepository::updateWebSideMenu($request, $id);
    }

    public static function deleteWebSideMenu($id){
        return WebSideMenuRepository::deleteWebSideMenu($id);
    }
}

