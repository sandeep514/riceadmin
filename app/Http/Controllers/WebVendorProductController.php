<?php

namespace App\Http\Controllers;

use App\PackingType;
use App\Services\WebVendorEquipmentProductService;
use App\Services\WebVendorPackagingProductService;
use App\Support\VendorProductCatalog;
use App\WebBusinessDetails;
use App\WebRiceBagProduct;
use Illuminate\Http\Request;

class WebVendorProductController extends Controller
{
    public function listByVendorId(Request $request, $id)
    {
        $vendorId = (int) $id;
        if ($vendorId <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Vendor id is required.',
            ], 422);
        }

        $vendorQuery = WebBusinessDetails::query()
            ->select([
                'id',
                'user_id',
                'company_name',
                'product',
                'contactPerson',
                'contactMobile',
                'address',
                'is_sntc_recommended',
                'is_active_listing',
                'selected_category',
            ]);

        $vendor = (clone $vendorQuery)
            ->where('id', $vendorId)
            ->orderByDesc('is_active_listing')
            ->orderByDesc('id')
            ->first();

        if ($vendor === null) {
            $vendor = (clone $vendorQuery)
                ->where('user_id', $vendorId)
                ->orderByDesc('is_active_listing')
                ->orderByDesc('id')
                ->first();
        }

        if ($vendor === null) {
            return response()->json([
                'status' => false,
                'message' => 'Vendor not found.',
            ], 404);
        }

        $userId = (int) ($vendor->user_id ?? 0);
        $ownerIds = array_values(array_unique(array_filter([$userId, (int) $vendor->id])));
        $kind = VendorProductCatalog::detectKindForVendor($vendor)
            ?? VendorProductCatalog::detectKindFromOwnerProducts($ownerIds);

        $vendorPayload = [
            'id' => (int) $vendor->id,
            'company_name' => $vendor->company_name,
            'product' => $vendor->product,
            'contactPerson' => $vendor->contactPerson,
            'contactMobile' => $vendor->contactMobile,
            'address' => $vendor->address,
            'recommended' => (int) ($vendor->is_sntc_recommended ?? 0),
            'has_products' => false,
            'vendorKind' => $kind,
        ];

        if ($ownerIds === []) {
            return response()->json([
                'status' => true,
                'message' => 'Vendor products fetched successfully.',
                'vendor' => $vendorPayload,
                'data' => [],
                'imageBasePath' => null,
            ], 200);
        }
        $data = collect();
        $imageUserId = $userId > 0 ? $userId : (int) $vendor->id;
        $imageBasePath = null;

        if ($kind === VendorProductCatalog::KIND_LAB_EQUIPMENT || $kind === VendorProductCatalog::KIND_MACHINERY_EQUIPMENT) {
            $service = $kind === VendorProductCatalog::KIND_LAB_EQUIPMENT
                ? WebVendorEquipmentProductService::lab()
                : WebVendorEquipmentProductService::machinery();

            $products = $service->verifiedProductsForOwners($ownerIds);
            $data = $products->map(fn ($product) => $service->serializeVendorProduct($product))->values();
            $imageBasePath = $service->imageBasePath($imageUserId);
        } elseif ($kind === VendorProductCatalog::KIND_CARTOON) {
            $service = WebVendorPackagingProductService::cartoon();
            $products = $service->verifiedProductsForOwners($ownerIds);
            $types = $service->typeOptions();
            $data = $products->map(fn ($product) => $service->serializeVendorProduct($product, $types))->values();
            $imageBasePath = $service->imageBasePath($imageUserId);
        } elseif ($kind === VendorProductCatalog::KIND_CYLINDER) {
            $service = WebVendorPackagingProductService::cylinder();
            $products = $service->verifiedProductsForOwners($ownerIds);
            $types = $service->typeOptions();
            $data = $products->map(fn ($product) => $service->serializeVendorProduct($product, $types))->values();
            $imageBasePath = $service->imageBasePath($imageUserId);
        } else {
            $products = WebRiceBagProduct::with(['packingSizes'])
                ->whereIn('user_id', $ownerIds)
                ->where('status', 1)
                ->whereHas('packingSizes')
                ->orderByDesc('id')
                ->get();

            $bagTypeIds = $products->pluck('bag_type_id')->filter()->unique()->values()->all();
            $bagTypes = $bagTypeIds === []
                ? collect()
                : PackingType::whereIn('id', $bagTypeIds)->pluck('name', 'id');

            $data = $products->map(function (WebRiceBagProduct $product) use ($bagTypes) {
                return $this->serializeRiceBagVendorProduct($product, $bagTypes);
            })->values();
            $imageBasePath = 'uploads/rice-bag-products/'.$imageUserId;
        }

        $vendorPayload['has_products'] = $data->isNotEmpty();

        return response()->json([
            'status' => true,
            'message' => 'Vendor products fetched successfully.',
            'vendor' => $vendorPayload,
            'data' => $data,
            'imageBasePath' => $imageBasePath,
        ], 200);
    }

    private function serializeRiceBagVendorProduct(WebRiceBagProduct $product, $bagTypes): array
    {
        $basePath = 'uploads/rice-bag-products/'.$product->user_id;

        $variants = $product->packingSizes->map(function ($size) use ($basePath) {
            return [
                'id' => (int) $size->id,
                'packingSizeId' => $size->packing_size_id !== null ? (int) $size->packing_size_id : null,
                'packingSize' => $size->packing_size,
                'rate' => $size->rate !== null ? (string) $size->rate : null,
                'gst' => $size->gst !== null ? (string) $size->gst : null,
                'totalPrice' => $size->total_price !== null ? (string) $size->total_price : null,
                'bagSize' => $size->bag_size,
                'bagWeight' => $size->bag_weight,
                'image' => $size->image,
                'imageUrl' => $size->image ? asset($basePath.'/'.$size->image) : null,
                'sortOrder' => (int) $size->sort_order,
            ];
        })->values()->all();

        $bagTypeName = null;
        if ($product->bag_type_id !== null) {
            $bagTypeName = $bagTypes[$product->bag_type_id] ?? null;
        }

        return [
            'id' => (int) $product->id,
            'bagTypeId' => $product->bag_type_id !== null ? (int) $product->bag_type_id : null,
            'bagTypeName' => $bagTypeName,
            'specification' => $product->specification,
            'description' => $product->description,
            'additionalInformation' => $product->additional_information,
            'packingFormId' => $product->packing_form_id !== null ? (int) $product->packing_form_id : null,
            'packingForm' => $product->packing_form,
            'variants' => $variants,
        ];
    }
}
