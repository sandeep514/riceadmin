<?php

namespace App\Http\Controllers;

use App\Services\WebVendorPackagingProductService;
use App\WebCartoonProduct;
use Illuminate\Http\Request;
use Session;

class WebCartoonProductController extends Controller
{
    private WebVendorPackagingProductService $service;

    public function __construct()
    {
        $this->service = WebVendorPackagingProductService::cartoon();
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

    public function deleteImage(Request $request, $imageId)
    {
        return $this->service->deleteImage($request, $imageId);
    }

    public function showProductsToAdmin()
    {
        $products = WebCartoonProduct::with(['user:id,name,email,mobile', 'variants'])
            ->orderByDesc('id')
            ->get();

        $types = $this->service->typeOptions();

        return view('webCartoonProducts.list', compact('products', 'types'));
    }

    public function showProductToAdmin($id)
    {
        $product = WebCartoonProduct::with([
            'user:id,name,email,mobile',
            'variants',
        ])->findOrFail((int) $id);

        $types = $this->service->typeOptions();
        $imageBasePath = $this->service->imageBasePath((int) $product->user_id);

        return view('webCartoonProducts.show', compact('product', 'types', 'imageBasePath'));
    }

    public function toggleStatus(Request $request, $id)
    {
        $result = $this->service->toggleStatus((int) $id, $request->input('reason'));
        if ($result === false) {
            Session::flash('error', 'Error|Cartoon product not found.');
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
