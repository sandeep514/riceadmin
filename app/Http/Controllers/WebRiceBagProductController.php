<?php

namespace App\Http\Controllers;

use App\Packing;
use App\WebRiceBagProduct;
use App\WebRiceBagProductPackingSize;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
                'received_keys' => array_values(array_keys($request->except(['packing_sizes']))),
            ], 422);
        }

        $product = DB::transaction(function () use ($request) {
            $attrs = $this->payloadToAttributes($request);
            $attrs['status'] = 1;

            $product = WebRiceBagProduct::create($attrs);
            $this->syncPackingSizes($product, $request->input('packingSizes', []), $request, replace: true);

            return $product->load(['packingSizes']);
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
                'received_keys' => array_values(array_keys($request->except(['packing_sizes']))),
            ], 422);
        }

        $product = WebRiceBagProduct::with(['packingSizes'])->find((int) $request->input('id'));
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
                $this->syncPackingSizes($product, $request->input('packingSizes', []), $request, replace: true);
            }

            return $product->load(['packingSizes']);
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
        $products = WebRiceBagProduct::with(['packingSizes'])
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
        $product = WebRiceBagProduct::with(['packingSizes'])->find((int) $id);
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
        $product = WebRiceBagProduct::with('packingSizes')->find((int) $id);
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
        foreach ($product->packingSizes as $size) {
            if ($size->image) {
                $filePath = $basePath . '/' . $size->image;
                if (is_file($filePath)) {
                    @unlink($filePath);
                }
            }
        }

        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Rice bag product deleted successfully.',
        ], 200);
    }

    /** Delete image for a packing size row (route param is packing_sizes.id). */
    public function deleteImage(Request $request, $imageId)
    {
        $size = WebRiceBagProductPackingSize::find((int) $imageId);
        if ($size === null) {
            return response()->json([
                'status' => false,
                'message' => 'Packing size image not found.',
            ], 404);
        }

        $product = WebRiceBagProduct::find($size->product_id);
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

        if ($size->image) {
            $filePath = public_path($this->imageBasePath((int) $product->user_id) . '/' . $size->image);
            if (is_file($filePath)) {
                @unlink($filePath);
            }
            $size->update(['image' => null]);
        }

        $product->load(['packingSizes']);

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
            'bag_type_id' => ['required', 'integer'],
            'specification' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'additional_information' => ['nullable', 'string'],
            'packing_form_id' => ['required', 'integer'],
            'packing_form' => ['required', 'string', 'in:' . implode(',', $packingForms)],
            'packingSizes' => ['required', 'array', 'min:1'],
            'packingSizes.*.packingSizeId' => ['required', 'integer'],
            'packingSizes.*.packingSize' => ['nullable', 'string', 'max:255'],
            'packingSizes.*.rate' => ['required', 'numeric'],
            'packingSizes.*.gst' => ['nullable', 'numeric'],
            'packingSizes.*.totalPrice' => ['nullable', 'numeric'],
            'packingSizes.*.bagSize' => ['nullable', 'string', 'max:255'],
            'packingSizes.*.bagWeight' => ['nullable', 'string', 'max:64'],
        ];
    }

    private function updateRules(): array
    {
        $packingForms = array_values(Packing::$packingForms);

        return [
            'id' => ['required', 'integer', 'exists:web_rice_bag_products,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'bag_type_id' => ['sometimes', 'required', 'integer'],
            'specification' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'additional_information' => ['nullable', 'string'],
            'packing_form_id' => ['sometimes', 'required', 'integer'],
            'packing_form' => ['sometimes', 'required', 'string', 'in:' . implode(',', $packingForms)],
            'packingSizes' => ['sometimes', 'array', 'min:1'],
            'packingSizes.*.packingSizeId' => ['required', 'integer'],
            'packingSizes.*.packingSize' => ['nullable', 'string', 'max:255'],
            'packingSizes.*.rate' => ['required', 'numeric'],
            'packingSizes.*.gst' => ['nullable', 'numeric'],
            'packingSizes.*.totalPrice' => ['nullable', 'numeric'],
            'packingSizes.*.bagSize' => ['nullable', 'string', 'max:255'],
            'packingSizes.*.bagWeight' => ['nullable', 'string', 'max:64'],
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

        if (! $request->filled('bag_type_id') && ! $request->filled('bagTypeId')) {
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
            'bagTypeId' => 'bag_type_id',
            'additionalInformation' => 'additional_information',
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

        // One product = one packing form. Frontend may send packing_forms[] / packing_form_ids[] — use first value.
        $formId = $request->input('packing_form_id', $request->input('packingFormId'));
        $formLabel = $request->input('packing_form', $request->input('packingForm'));

        $formIds = $request->input('packing_form_ids', $request->input('packingFormIds'));
        $formLabels = $request->input('packing_forms', $request->input('packingForms'));
        if (is_array($formIds) && $formIds !== []) {
            $formId = $formIds[0];
        }
        if (is_array($formLabels) && $formLabels !== []) {
            $formLabel = $formLabels[0];
        }

        if (is_numeric($formLabel) && isset(Packing::$packingForms[(int) $formLabel])) {
            $formId = (int) $formLabel;
            $formLabel = Packing::$packingForms[$formId];
        } elseif (is_numeric($formId) && ($formLabel === null || $formLabel === '') && isset(Packing::$packingForms[(int) $formId])) {
            $formLabel = Packing::$packingForms[(int) $formId];
        }

        if ($formId !== null && $formId !== '') {
            $request->merge(['packing_form_id' => (int) $formId]);
        }
        if ($formLabel !== null && $formLabel !== '') {
            $request->merge(['packing_form' => $formLabel]);
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
            foreach ($packingSizes as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $normalizedRows[] = [
                    'packingSizeId' => $row['packingSizeId'] ?? $row['packing_size_id'] ?? null,
                    'packingSize' => $row['packingSize'] ?? $row['packing_size'] ?? null,
                    'rate' => $row['rate'] ?? null,
                    'gst' => $row['gst'] ?? null,
                    'totalPrice' => $row['totalPrice'] ?? $row['total_price'] ?? null,
                    'bagSize' => $row['bagSize'] ?? $row['bag_size'] ?? null,
                    'bagWeight' => $row['bagWeight'] ?? $row['bag_weight'] ?? null,
                    'existingImage' => $row['existingImage'] ?? $row['image'] ?? null,
                    '_index' => is_numeric($index) ? (int) $index : null,
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
            'bag_type_id' => 'bag_type_id',
            'specification' => 'specification',
            'description' => 'description',
            'additional_information' => 'additional_information',
            'packing_form_id' => 'packing_form_id',
            'packing_form' => 'packing_form',
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

    private function syncPackingSizes(WebRiceBagProduct $product, $packingSizes, Request $request, bool $replace = true): void
    {
        if (! is_array($packingSizes)) {
            $packingSizes = [];
        }

        if ($replace) {
            $basePath = public_path($this->imageBasePath((int) $product->user_id));
            $existing = WebRiceBagProductPackingSize::where('product_id', $product->id)->get();
            foreach ($existing as $old) {
                if ($old->image) {
                    $filePath = $basePath . '/' . $old->image;
                    if (is_file($filePath)) {
                        @unlink($filePath);
                    }
                }
            }
            WebRiceBagProductPackingSize::where('product_id', $product->id)->delete();
        }

        $sortOrder = 0;
        foreach ($packingSizes as $row) {
            if (! is_array($row)) {
                continue;
            }

            $sortOrder++;
            $index = $row['_index'] ?? ($sortOrder - 1);
            $imageName = $this->resolvePackingSizeImage($request, $product, $index, $row);

            WebRiceBagProductPackingSize::create([
                'product_id' => $product->id,
                'packing_size_id' => isset($row['packingSizeId']) && $row['packingSizeId'] !== ''
                    ? (int) $row['packingSizeId']
                    : null,
                'packing_size' => $row['packingSize'] ?? null,
                'rate' => $row['rate'] ?? null,
                'gst' => $row['gst'] ?? null,
                'total_price' => $row['totalPrice'] ?? null,
                'bag_size' => $row['bagSize'] ?? null,
                'bag_weight' => $row['bagWeight'] ?? null,
                'image' => $imageName,
                'sort_order' => $sortOrder,
            ]);
        }
    }

    private function resolvePackingSizeImage(Request $request, WebRiceBagProduct $product, $index, array $row): ?string
    {
        $file = $request->file("packing_sizes.{$index}.image")
            ?? $request->file("packingSizes.{$index}.image");

        if ($file instanceof UploadedFile && $file->isValid()) {
            return $this->storePackingSizeImage($product, $file);
        }

        $existing = $row['existingImage'] ?? null;
        if (is_string($existing) && $existing !== '' && ! str_contains($existing, '/')) {
            return $existing;
        }

        return null;
    }

    private function storePackingSizeImage(WebRiceBagProduct $product, UploadedFile $file): string
    {
        $dir = public_path($this->imageBasePath((int) $product->user_id));
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $newName = time() . '_' . uniqid('', true) . '.' . $ext;
        $file->move($dir, $newName);

        return $newName;
    }

    private function imageBasePath(int $userId): string
    {
        return 'uploads/rice-bag-products/' . $userId;
    }

    private function serializeProduct(WebRiceBagProduct $product): array
    {
        $basePath = $this->imageBasePath((int) $product->user_id);

        $packingSizes = $product->packingSizes->map(function (WebRiceBagProductPackingSize $size) use ($basePath) {
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
                'imageUrl' => $size->image ? asset($basePath . '/' . $size->image) : null,
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
        ];
    }
}
