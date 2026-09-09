<?php

namespace App\Http\Controllers;

use App\Services\WebCleaningAgentProductService;
use App\WebCleaningAgentProduct;
use Illuminate\Http\Request;
use Session;

class WebCleaningAgentProductController extends Controller
{
    private WebCleaningAgentProductService $service;

    public function __construct()
    {
        $this->service = new WebCleaningAgentProductService();
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
        $products = WebCleaningAgentProduct::with(['user:id,name,email,mobile', 'particulars.particular'])
            ->orderByDesc('id')
            ->get();

        return view('webCleaningAgentProducts.list', compact('products'));
    }

    public function showProductToAdmin($id)
    {
        $product = WebCleaningAgentProduct::with([
            'user:id,name,email,mobile',
            'particulars.particular',
        ])->findOrFail((int) $id);

        return view('webCleaningAgentProducts.show', compact('product'));
    }

    public function toggleStatus(Request $request, $id)
    {
        $result = $this->service->toggleStatus((int) $id, $request->input('reason'));
        if ($result === false) {
            Session::flash('error', 'Error|Cleaning agent product not found.');

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
