<?php

namespace App\Http\Controllers;

use App\Services\WebVendorEquipmentProductService;
use App\WebMachineryEquipmentProduct;
use Illuminate\Http\Request;
use Session;

class WebMachineryEquipmentProductController extends Controller
{
    private WebVendorEquipmentProductService $service;

    public function __construct()
    {
        $this->service = WebVendorEquipmentProductService::machinery();
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
        $products = WebMachineryEquipmentProduct::with(['user:id,name,email,mobile', 'variants'])
            ->orderByDesc('id')
            ->get();

        return view('webMachineryEquipmentProducts.list', compact('products'));
    }

    public function showProductToAdmin($id)
    {
        $product = WebMachineryEquipmentProduct::with([
            'user:id,name,email,mobile',
            'variants',
        ])->findOrFail((int) $id);

        $imageBasePath = $this->service->imageBasePath((int) $product->user_id);

        return view('webMachineryEquipmentProducts.show', compact('product', 'imageBasePath'));
    }

    public function toggleStatus($id)
    {
        if (! $this->service->toggleStatus((int) $id)) {
            Session::flash('error', 'Error|Machinery equipment product not found.');
            return back();
        }

        Session::flash('success', 'Success|Status updated successfully.');
        return back();
    }
}
