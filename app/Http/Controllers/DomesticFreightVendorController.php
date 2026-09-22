<?php

namespace App\Http\Controllers;

use App\Category;
use App\ServiceProviderUserMap;
use App\User;
use App\WebBusinessDetails;
use App\WebDomesticFreightProduct;

class DomesticFreightVendorController extends Controller
{
    public function index()
    {
        $categoryIds = Category::query()
            ->where(function ($query) {
                $query->where(function ($inner) {
                    $inner->whereRaw('LOWER(category) LIKE ?', ['%domestic%'])
                        ->where(function ($transport) {
                            $transport->whereRaw('LOWER(category) LIKE ?', ['%transport%'])
                                ->orWhereRaw('LOWER(category) LIKE ?', ['%freight%']);
                        });
                })->orWhereRaw('LOWER(category) LIKE ?', ['%domestic transporter%']);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();

        $businessUserIds = WebBusinessDetails::query()
            ->where(function ($query) use ($categoryIds) {
                if ($categoryIds !== []) {
                    $query->whereIn('selected_category', $categoryIds)
                        ->orWhereIn('selected_category', array_map('strval', $categoryIds));
                }

                $query->orWhere(function ($inner) {
                    $inner->whereRaw('LOWER(selected_category) LIKE ?', ['%domestic%'])
                        ->where(function ($transport) {
                            $transport->whereRaw('LOWER(selected_category) LIKE ?', ['%transport%'])
                                ->orWhereRaw('LOWER(selected_category) LIKE ?', ['%freight%']);
                        });
                })->orWhereRaw('LOWER(selected_category) LIKE ?', ['%domestic transporter%']);
            })
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->all();

        $mapUserIds = ServiceProviderUserMap::query()
            ->where(function ($query) {
                $query->where(function ($inner) {
                    $inner->whereRaw('LOWER(type) LIKE ?', ['%domestic%'])
                        ->where(function ($transport) {
                            $transport->whereRaw('LOWER(type) LIKE ?', ['%transport%'])
                                ->orWhereRaw('LOWER(type) LIKE ?', ['%freight%']);
                        });
                })
                    ->orWhereRaw('LOWER(type) LIKE ?', ['%domestic transporter%'])
                    ->orWhereRaw('LOWER(`key`) LIKE ?', ['%domestic%freight%'])
                    ->orWhereRaw('LOWER(`key`) LIKE ?', ['%domestic_freight%'])
                    ->orWhereRaw('LOWER(`value`) LIKE ?', ['%domestic%transport%'])
                    ->orWhereRaw('LOWER(`value`) LIKE ?', ['%domestic%freight%']);
            })
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->all();

        $productUserIds = WebDomesticFreightProduct::query()
            ->distinct()
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->all();

        $userIds = array_values(array_unique(array_merge($businessUserIds, $mapUserIds, $productUserIds)));

        $vendors = $userIds === []
            ? collect()
            : User::query()
                ->with(['getWebBusinessDetails.getCategoryDetails'])
                ->whereIn('id', $userIds)
                ->orderByDesc('id')
                ->get();

        return view('domestic-freight-vendors.index', compact('vendors'));
    }
}
