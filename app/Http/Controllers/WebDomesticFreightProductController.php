<?php

namespace App\Http\Controllers;

use App\Services\WebDomesticFreightProductService;
use Illuminate\Http\Request;

class WebDomesticFreightProductController extends Controller
{
    private WebDomesticFreightProductService $service;

    public function __construct()
    {
        $this->service = new WebDomesticFreightProductService();
    }

    public function sync(Request $request)
    {
        return $this->service->sync($request);
    }

    public function listByUser(Request $request, $userId)
    {
        return $this->service->listByUser($request, $userId);
    }

    public function statesSummary(Request $request)
    {
        return $this->service->statesSummary($request);
    }

    public function listByState(Request $request, $stateId = null)
    {
        return $this->service->listByState($request, $stateId);
    }

    public function delete(Request $request, $id)
    {
        return $this->service->delete($request, $id);
    }
}
