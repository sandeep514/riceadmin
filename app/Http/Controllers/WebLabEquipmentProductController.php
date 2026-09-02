<?php

namespace App\Http\Controllers;

use App\Services\WebVendorEquipmentProductService;
use App\WebLabEquipmentProduct;
use Illuminate\Http\Request;
use Session;

class WebLabEquipmentProductController extends Controller
{
    private WebVendorEquipmentProductService $service;

    public function __construct()
    {
        $this->service = WebVendorEquipmentProductService::lab();
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

    public function deleteCatalogue(Request $request, $variantId)
    {
        return $this->service->deleteCatalogue($request, $variantId);
    }

    public function showProductsToAdmin()
    {
        $products = WebLabEquipmentProduct::with(['user:id,name,email,mobile', 'variants'])
            ->orderByDesc('id')
            ->get();

        return view('webLabEquipmentProducts.list', compact('products'));
    }

    public function showProductToAdmin($id)
    {
        $product = WebLabEquipmentProduct::with([
            'user:id,name,email,mobile',
            'variants',
        ])->findOrFail((int) $id);

        $imageBasePath = $this->service->imageBasePath((int) $product->user_id);

        return view('webLabEquipmentProducts.show', compact('product', 'imageBasePath'));
    }

    public function toggleStatus(Request $request, $id)
    {
        $result = $this->service->toggleStatus((int) $id, $request->input('reason'));
        if ($result === false) {
            Session::flash('error', 'Error|Lab equipment product not found.');
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
