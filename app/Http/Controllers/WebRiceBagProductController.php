<?php

namespace App\Http\Controllers;

use App\Packing;
use App\WebRiceBagProduct;
use App\WebRiceBagProductImage;
use App\WebRiceBagProductPackingSize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WebRiceBagProductController extends Controller
{
    public function create(Request $request)
    {
        $this->normalizeIncomingPayload($request);

        if ($denied = $this->denyIfUserMismatch($request)) {
            return $denied;
        }

        $validator = Validator::make($request->all(), $this->createRules());
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $product = DB::transaction(function () use ($request) {
            $attrs = $this->payloadToAttributes($request);
            $attrs['status'] = 1;

            $product = WebRiceBagProduct::create($attrs);
            $this->syncPackingSizes($product, $request->input('packingSizes', []), replace: true);
            $this->storeImages($request, $product);

            return $product->load(['images', 'packingSizes']);
        });

        return response()->json([
            'status' => true,
            'message' => 'Rice bag product saved successfully.',
            'data' => $this->serializeProduct($product),
            'imageBasePath' => $this->imageBasePath((int) $product->user_id),
        ], 200);
    }

    public function update(Request $request)
    {
        $this->normalizeIncomingPayload($request);

        if ($denied = $this->denyIfUserMismatch($request)) {
            return $denied;
        }

        $validator = Validator::make($request->all(), $this->updateRules());
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $product = WebRiceBagProduct::with(['images', 'packingSizes'])->find((int) $request->input('id'));
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => 'Rice bag product not found.',
            ], 404);
        }

        $authUser = $request->user();
        if ($authUser && (int) $product->user_id !== (int) $authUser->id) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden: You are not allowed to update this product.',
            ], 403);
        }

        if ($request->filled('user_id') && (int) $request->input('user_id') !== (int) $product->user_id) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden: Product does not belong to this user.',
            ], 403);
        }

        $product = DB::transaction(function () use ($request, $product) {
            $attrs = $this->payloadToAttributes($request, partial: true);
            if (! empty($attrs)) {
                $product->fill($attrs);
                $product->save();
            }

            if ($request->exists('packingSizes')) {
                $this->syncPackingSizes($product, $request->input('packingSizes', []), replace: true);
            }

            $this->storeImages($request, $product);

            return $product->load(['images', 'packingSizes']);
        });

        return response()->json([
            'status' => true,
            'message' => 'Rice bag product updated successfully.',
            'data' => $this->serializeProduct($product),
            'imageBasePath' => $this->imageBasePath((int) $product->user_id),
        ], 200);
    }

    public function listByUser(Request $request, $userId)
    {
        $products = WebRiceBagProduct::with(['images', 'packingSizes'])
            ->where('user_id', (int) $userId)
            ->orderByDesc('id')
            ->get()
            ->map(function (WebRiceBagProduct $product) {
                return $this->serializeProduct($product);
            })
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Rice bag products fetched successfully.',
            'data' => $products,
            'imageBasePath' => $this->imageBasePath((int) $userId),
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $product = WebRiceBagProduct::with(['images', 'packingSizes'])->find((int) $id);
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => 'Rice bag product not found.',
            ], 404);
        }

        $authUser = $request->user();
        if ($authUser && (int) $product->user_id !== (int) $authUser->id) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden: You are not allowed to access this product.',
            ], 403);
        }

        return response()->json([
            'status' => true,
            'message' => 'Rice bag product fetched successfully.',
            'data' => $this->serializeProduct($product),
            'imageBasePath' => $this->imageBasePath((int) $product->user_id),
        ], 200);
    }

    public function delete(Request $request, $id)
    {
        $product = WebRiceBagProduct::with('images')->find((int) $id);
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => 'Rice bag product not found.',
            ], 404);
        }

        $authUser = $request->user();
        if ($authUser && (int) $product->user_id !== (int) $authUser->id) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden: You are not allowed to delete this product.',
            ], 403);
        }

        $basePath = public_path($this->imageBasePath((int) $product->user_id));
        foreach ($product->images as $image) {
            $filePath = $basePath . '/' . $image->file_name;
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Rice bag product deleted successfully.',
        ], 200);
    }

    public function deleteImage(Request $request, $imageId)
    {
        $image = WebRiceBagProductImage::find((int) $imageId);
        if ($image === null) {
            return response()->json([
                'status' => false,
                'message' => 'Image not found.',
            ], 404);
        }

        $product = WebRiceBagProduct::find($image->product_id);
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => 'Rice bag product not found.',
            ], 404);
        }

        $authUser = $request->user();
        if ($authUser && (int) $product->user_id !== (int) $authUser->id) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden: You are not allowed to delete this image.',
            ], 403);
        }

        $filePath = public_path($this->imageBasePath((int) $product->user_id) . '/' . $image->file_name);
        if (is_file($filePath)) {
            @unlink($filePath);
        }

        $image->delete();

        $product->load(['images', 'packingSizes']);

        return response()->json([
            'status' => true,
            'message' => 'Image deleted successfully.',
            'data' => $this->serializeProduct($product),
            'imageBasePath' => $this->imageBasePath((int) $product->user_id),
        ], 200);
    }

    private function createRules(): array
    {
        return array_merge($this->parentRules(required: true), $this->packingSizesRules(required: true), [
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
    }

    private function updateRules(): array
    {
        return array_merge([
            'id' => ['required', 'integer', 'exists:web_rice_bag_products,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], $this->parentRules(required: false), $this->packingSizesRules(required: false));
    }

    private function parentRules(bool $required): array
    {
        $req = $required ? ['required'] : ['sometimes', 'required'];

        return [
            'user_id' => $required
                ? ['required', 'integer', 'exists:users,id']
                : ['nullable', 'integer', 'exists:users,id'],
            'productName' => array_merge($req, ['string', 'max:255']),
            'riceNameId' => array_merge($req, ['integer']),
            'riceFormId' => array_merge($req, ['integer']),
            'riceForm' => ['nullable', 'string', 'max:255'],
            'bagColor' => array_merge($req, ['string', 'max:64']),
            'printType' => array_merge($req, ['string', 'max:64']),
            'description' => ['nullable', 'string'],
        ];
    }

    private function packingSizesRules(bool $required): array
    {
        $packingForms = array_values(Packing::$packingForms);
        $listRule = $required ? ['required', 'array', 'min:1'] : ['sometimes', 'array', 'min:1'];

        return [
            'packingSizes' => $listRule,
            'packingSizes.*.packingId' => ['required', 'integer'],
            'packingSizes.*.packing' => ['nullable', 'string', 'max:255'],
            'packingSizes.*.packingForm' => ['required', 'string', 'in:' . implode(',', $packingForms)],
            'packingSizes.*.availableQuantity' => ['required', 'numeric'],
            'packingSizes.*.price' => ['required', 'numeric'],
        ];
    }

    private function normalizeIncomingPayload(Request $request): void
    {
        if ($request->filled('userId') && ! $request->filled('user_id')) {
            $request->merge(['user_id' => $request->input('userId')]);
        }

        $packingSizes = $request->input('packingSizes');
        if (is_string($packingSizes) && $packingSizes !== '') {
            $decoded = json_decode($packingSizes, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $request->merge(['packingSizes' => $decoded]);
            }
        }
    }

    private function denyIfUserMismatch(Request $request)
    {
        $authUser = $request->user();
        if (! $authUser) {
            return null;
        }

        $payloadUserId = $request->input('user_id', $request->input('userId'));
        if ($payloadUserId !== null && $payloadUserId !== '' && (int) $payloadUserId !== (int) $authUser->id) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden: You are not allowed to perform this action for another user.',
            ], 403);
        }

        return null;
    }

    private function payloadToAttributes(Request $request, bool $partial = false): array
    {
        $map = [
            'productName' => 'product_name',
            'riceNameId' => 'rice_name_id',
            'riceFormId' => 'rice_form_id',
            'riceForm' => 'rice_form',
            'bagColor' => 'bag_color',
            'printType' => 'print_type',
            'description' => 'description',
        ];

        $attrs = [];

        if ((! $partial || $request->filled('user_id')) && $request->filled('user_id')) {
            $attrs['user_id'] = (int) $request->input('user_id');
        }

        foreach ($map as $inputKey => $column) {
            if ($partial && ! $request->exists($inputKey)) {
                continue;
            }
            if (! $request->exists($inputKey)) {
                continue;
            }

            $value = $request->input($inputKey);
            if (in_array($column, ['rice_name_id', 'rice_form_id'], true)) {
                $attrs[$column] = $value === null || $value === '' ? null : (int) $value;
            } else {
                $attrs[$column] = $value;
            }
        }

        return $attrs;
    }

    private function syncPackingSizes(WebRiceBagProduct $product, $packingSizes, bool $replace = true): void
    {
        if (! is_array($packingSizes)) {
            $packingSizes = [];
        }

        if ($replace) {
            WebRiceBagProductPackingSize::where('product_id', $product->id)->delete();
        }

        $sortOrder = 0;
        foreach ($packingSizes as $row) {
            if (! is_array($row)) {
                continue;
            }

            $sortOrder++;
            WebRiceBagProductPackingSize::create([
                'product_id' => $product->id,
                'packing_id' => isset($row['packingId']) && $row['packingId'] !== ''
                    ? (int) $row['packingId']
                    : null,
                'packing' => $row['packing'] ?? null,
                'packing_form' => $row['packingForm'] ?? null,
                'available_quantity' => $row['availableQuantity'] ?? null,
                'price' => $row['price'] ?? null,
                'sort_order' => $sortOrder,
            ]);
        }
    }

    private function storeImages(Request $request, WebRiceBagProduct $product): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $files = $request->file('images');
        if (! is_array($files)) {
            $files = [$files];
        }

        $dir = public_path($this->imageBasePath((int) $product->user_id));
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $sortOrder = (int) WebRiceBagProductImage::where('product_id', $product->id)->max('sort_order');

        foreach ($files as $file) {
            if ($file === null || ! $file->isValid()) {
                continue;
            }

            $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
            $newName = time() . '_' . uniqid('', true) . '.' . $ext;
            $file->move($dir, $newName);

            $sortOrder++;
            WebRiceBagProductImage::create([
                'product_id' => $product->id,
                'file_name' => $newName,
                'sort_order' => $sortOrder,
            ]);
        }
    }

    private function imageBasePath(int $userId): string
    {
        return 'uploads/rice-bag-products/' . $userId;
    }

    private function serializeProduct(WebRiceBagProduct $product): array
    {
        $basePath = $this->imageBasePath((int) $product->user_id);

        $images = $product->images->map(function (WebRiceBagProductImage $image) use ($basePath) {
            return [
                'id' => (int) $image->id,
                'fileName' => $image->file_name,
                'url' => asset($basePath . '/' . $image->file_name),
                'sortOrder' => (int) $image->sort_order,
            ];
        })->values()->all();

        $packingSizes = $product->packingSizes->map(function (WebRiceBagProductPackingSize $size) {
            return [
                'id' => (int) $size->id,
                'packingId' => $size->packing_id !== null ? (int) $size->packing_id : null,
                'packing' => $size->packing,
                'packingForm' => $size->packing_form,
                'availableQuantity' => $size->available_quantity !== null ? (string) $size->available_quantity : null,
                'price' => $size->price !== null ? (string) $size->price : null,
                'sortOrder' => (int) $size->sort_order,
            ];
        })->values()->all();

        return [
            'id' => (int) $product->id,
            'userId' => (int) $product->user_id,
            'productName' => $product->product_name,
            'riceNameId' => $product->rice_name_id !== null ? (int) $product->rice_name_id : null,
            'riceFormId' => $product->rice_form_id !== null ? (int) $product->rice_form_id : null,
            'riceForm' => $product->rice_form,
            'bagColor' => $product->bag_color,
            'printType' => $product->print_type,
            'description' => $product->description,
            'status' => (int) $product->status,
            'packingSizes' => $packingSizes,
            'images' => $images,
        ];
    }
}
