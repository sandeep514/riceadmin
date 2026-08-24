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
                'message' => 'Validation failed. Expected: userId, bagTypeId, specification, description, additionalInformation, packingFormId, packingForm, packingSizes[{packingId,packing,availableQuantity,price}], images.',
                'errors' => $validator->errors(),
                'received_keys' => array_values(array_keys($request->except(['images']))),
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
                'received_keys' => array_values(array_keys($request->except(['images']))),
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
        $packingForms = array_values(Packing::$packingForms);

        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'bagTypeId' => ['required', 'integer'],
            'specification' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'additionalInformation' => ['nullable', 'string'],
            'packingFormId' => ['required', 'integer'],
            'packingForm' => ['required', 'string', 'in:' . implode(',', $packingForms)],
            'packingSizes' => ['required', 'array', 'min:1'],
            'packingSizes.*.packingId' => ['required', 'integer'],
            'packingSizes.*.packing' => ['nullable', 'string', 'max:255'],
            'packingSizes.*.availableQuantity' => ['required', 'numeric'],
            'packingSizes.*.price' => ['required', 'numeric'],
            'images' => ['nullable'],
        ];
    }

    private function updateRules(): array
    {
        $packingForms = array_values(Packing::$packingForms);

        return [
            'id' => ['required', 'integer', 'exists:web_rice_bag_products,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'bagTypeId' => ['sometimes', 'required', 'integer'],
            'specification' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'additionalInformation' => ['nullable', 'string'],
            'packingFormId' => ['sometimes', 'required', 'integer'],
            'packingForm' => ['sometimes', 'required', 'string', 'in:' . implode(',', $packingForms)],
            'packingSizes' => ['sometimes', 'array', 'min:1'],
            'packingSizes.*.packingId' => ['required', 'integer'],
            'packingSizes.*.packing' => ['nullable', 'string', 'max:255'],
            'packingSizes.*.availableQuantity' => ['required', 'numeric'],
            'packingSizes.*.price' => ['required', 'numeric'],
            'images' => ['nullable'],
        ];
    }

    private function normalizeIncomingPayload(Request $request): void
    {
        foreach (['payload', 'data', 'body', 'json'] as $wrapKey) {
            if (! $request->exists($wrapKey)) {
                continue;
            }
            $wrapped = $request->input($wrapKey);
            if (is_string($wrapped) && trim($wrapped) !== '') {
                $decoded = json_decode($wrapped, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $request->merge($decoded);
                }
            } elseif (is_array($wrapped)) {
                $request->merge($wrapped);
            }
        }

        if (! $request->filled('bagTypeId') && ! $request->filled('bag_type_id')) {
            $raw = $request->getContent();
            if (is_string($raw) && $raw !== '') {
                $trimmed = ltrim($raw);
                if ($trimmed !== '' && ($trimmed[0] === '{' || $trimmed[0] === '[')) {
                    $decoded = json_decode($raw, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        if (array_is_list($decoded) && isset($decoded[0]) && is_array($decoded[0])) {
                            $request->merge($decoded[0]);
                        } else {
                            $request->merge($decoded);
                        }
                    }
                }
            }
        }

        $aliases = [
            'bag_type_id' => 'bagTypeId',
            'additional_information' => 'additionalInformation',
            'packing_form_id' => 'packingFormId',
            'packing_form' => 'packingForm',
            'packing_sizes' => 'packingSizes',
        ];
        foreach ($aliases as $from => $to) {
            if ($request->exists($from) && ! $request->exists($to)) {
                $request->merge([$to => $request->input($from)]);
            }
        }

        if ($request->filled('userId') && ! $request->filled('user_id')) {
            $request->merge(['user_id' => $request->input('userId')]);
        }
        if ($request->filled('user_id') && ! $request->filled('userId')) {
            $request->merge(['userId' => $request->input('user_id')]);
        }

        // packingFormId 1/2 -> label Normal/Gusset when packingForm missing
        if ($request->filled('packingFormId') && ! $request->filled('packingForm')) {
            $formId = (int) $request->input('packingFormId');
            if (isset(Packing::$packingForms[$formId])) {
                $request->merge(['packingForm' => Packing::$packingForms[$formId]]);
            }
        }
        if ($request->filled('packingForm') && is_numeric($request->input('packingForm'))) {
            $formId = (int) $request->input('packingForm');
            if (isset(Packing::$packingForms[$formId])) {
                $request->merge([
                    'packingFormId' => $formId,
                    'packingForm' => Packing::$packingForms[$formId],
                ]);
            }
        }

        $packingSizes = $request->input('packingSizes', $request->input('packing_sizes'));
        if (is_string($packingSizes) && trim($packingSizes) !== '') {
            $decoded = json_decode($packingSizes, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $packingSizes = $decoded;
            }
        }

        if (is_array($packingSizes) && ! empty($packingSizes)) {
            $normalizedRows = [];
            foreach ($packingSizes as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $normalizedRows[] = [
                    'packingId' => $row['packingId'] ?? $row['packing_id'] ?? null,
                    'packing' => $row['packing'] ?? $row['packing_name'] ?? null,
                    'availableQuantity' => $row['availableQuantity'] ?? $row['available_quantity'] ?? null,
                    'price' => $row['price'] ?? null,
                ];
            }
            $request->merge(['packingSizes' => $normalizedRows]);
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
            'bagTypeId' => 'bag_type_id',
            'specification' => 'specification',
            'description' => 'description',
            'additionalInformation' => 'additional_information',
            'packingFormId' => 'packing_form_id',
            'packingForm' => 'packing_form',
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
            if (in_array($column, ['bag_type_id', 'packing_form_id'], true)) {
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
                'availableQuantity' => $size->available_quantity !== null ? (string) $size->available_quantity : null,
                'price' => $size->price !== null ? (string) $size->price : null,
                'sortOrder' => (int) $size->sort_order,
            ];
        })->values()->all();

        return [
            'id' => (int) $product->id,
            'userId' => (int) $product->user_id,
            'bagTypeId' => $product->bag_type_id !== null ? (int) $product->bag_type_id : null,
            'specification' => $product->specification,
            'description' => $product->description,
            'additionalInformation' => $product->additional_information,
            'packingFormId' => $product->packing_form_id !== null ? (int) $product->packing_form_id : null,
            'packingForm' => $product->packing_form,
            'status' => (int) $product->status,
            'packingSizes' => $packingSizes,
            'images' => $images,
        ];
    }
}
