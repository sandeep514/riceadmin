<?php

namespace App\Http\Controllers;

use App\Services\WebClearingAgentProductService;
use App\WebClearingAgentProduct;
use Illuminate\Http\Request;
use Session;

class WebClearingAgentProductController extends Controller
{
    private WebClearingAgentProductService $service;

    public function __construct()
    {
        $this->service = new WebClearingAgentProductService();
    }

    public function create(Request $request)
    {
        return $this->service->create($request);
    }

    public function update(Request $request)
    {
        return $this->service->update($request);
    }

    public function listByUser(Request $request, $userId)
    {
        return $this->service->listByUser($request, $userId);
    }

    public function show(Request $request, $id)
    {
        return $this->service->show($request, $id);
    }

    public function delete(Request $request, $id)
    {
        return $this->service->delete($request, $id);
    }

    public function showProductsToAdmin()
    {
        $products = WebClearingAgentProduct::with([
            'user:id,name,email,mobile',
            'particulars.particular',
            'containerSizeRel',
            'portTypeRel',
        ])
            ->orderByDesc('id')
            ->get();

        return view('webClearingAgentProducts.list', compact('products'));
    }

    public function showProductToAdmin($id)
    {
        $product = WebClearingAgentProduct::with([
            'user:id,name,email,mobile',
            'particulars.particular',
            'containerSizeRel',
            'portTypeRel',
            'icdLocationRel',
            'indianPortRel',
            'destinationPortRel',
        ])->findOrFail((int) $id);

        return view('webClearingAgentProducts.show', compact('product'));
    }

    public function toggleStatus(Request $request, $id)
    {
        $result = $this->service->toggleStatus((int) $id, $request->input('reason'));
        if ($result === false) {
            Session::flash('error', 'Error|Clearing agent product not found.');

            return back();
        }
        if (! empty($result['missing_reason'])) {
            Session::flash('error', 'Error|Please provide a reason to de-activate this product.');

            return back();
        }

        Session::flash(
            'success',
            ! empty($result['deactivated'])
                ? 'Success|Product de-activated and vendor notified.'
                : 'Success|Product verified successfully.'
        );

        return back();
    }
}
