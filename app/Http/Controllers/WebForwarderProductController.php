<?php

namespace App\Http\Controllers;

use App\Category;
use App\ServiceProviderUserMap;
use App\Services\WebForwarderProductService;
use App\User;
use App\WebBusinessDetails;
use App\WebForwarderProduct;
use Illuminate\Http\Request;
use Session;

class WebForwarderProductController extends Controller
{
    private WebForwarderProductService $service;

    public function __construct()
    {
        $this->service = new WebForwarderProductService();
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

    public function showVendorsToAdmin()
    {
        $productUserIds = WebForwarderProduct::query()
            ->distinct()
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->all();

        $categoryIds = Category::query()
            ->where(function ($query) {
                $query->whereRaw('LOWER(category) LIKE ?', ['%forwarder%'])
                    ->orWhereRaw('LOWER(category) LIKE ?', ['%forwarding%']);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $businessUserIds = WebBusinessDetails::query()
            ->where(function ($query) use ($categoryIds) {
                if ($categoryIds !== []) {
                    $query->whereIn('selected_category', $categoryIds)
                        ->orWhereIn('selected_category', array_map('strval', $categoryIds));
                }
                $query->orWhereRaw('LOWER(selected_category) LIKE ?', ['%forwarder%'])
                    ->orWhereRaw('LOWER(selected_category) LIKE ?', ['%forwarding%']);
            })
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->all();

        $mapUserIds = ServiceProviderUserMap::query()
            ->where(function ($query) {
                $query->whereRaw('LOWER(type) LIKE ?', ['%forwarder%'])
                    ->orWhereRaw('LOWER(type) LIKE ?', ['%forwarding%'])
                    ->orWhereRaw('LOWER(`key`) LIKE ?', ['%forwarder%'])
                    ->orWhereRaw('LOWER(`value`) LIKE ?', ['%forwarder%']);
            })
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->all();

        $userIds = array_values(array_unique(array_merge($productUserIds, $businessUserIds, $mapUserIds)));

        $vendors = $userIds === []
            ? collect()
            : User::query()
                ->with(['getWebBusinessDetails.getCategoryDetails'])
                ->whereIn('id', $userIds)
                ->orderByDesc('id')
                ->get();

        $productCounts = $userIds === []
            ? collect()
            : WebForwarderProduct::query()
                ->selectRaw('user_id, COUNT(*) as total, SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as verified')
                ->whereIn('user_id', $userIds)
                ->groupBy('user_id')
                ->get()
                ->keyBy('user_id');

        return view('webForwarderProducts.vendors', compact('vendors', 'productCounts'));
    }

    public function showProductsToAdmin()
    {
        $products = WebForwarderProduct::with([
            'user:id,name,email,mobile',
            'charges',
            'containerSizes.containerSizeRel',
            'portTypeRel',
            'indianPortRel',
            'destinationPortRel',
        ])
            ->orderByDesc('id')
            ->get();

        return view('webForwarderProducts.list', compact('products'));
    }

    public function showProductToAdmin($id)
    {
        $product = WebForwarderProduct::with([
            'user:id,name,email,mobile',
            'charges.titleRel',
            'containerSizes.containerSizeRel',
            'portTypeRel',
            'icdLocationRel',
            'indianPortRel',
            'regionRel',
            'countryRel',
            'destinationPortRel.region',
            'destinationPortRel.country',
        ])->findOrFail((int) $id);

        return view('webForwarderProducts.show', compact('product'));
    }

    public function toggleStatus(Request $request, $id)
    {
        $result = $this->service->toggleStatus((int) $id, $request->input('reason'));
        if ($result === false) {
            Session::flash('error', 'Error|Forwarder product not found.');

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
