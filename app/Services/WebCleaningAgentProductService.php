<?php

namespace App\Services;

use App\CleaningAgentParticularMap;
use App\VendorContainerParticular;
use App\WebCleaningAgentProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WebCleaningAgentProductService
{
    public function create(Request $request)
    {
        $this->normalizeIncomingPayload($request);

        if ($denied = $this->denyIfUserMismatch($request)) {
            return $denied;
        }

        $validator = Validator::make($request->all(), $this->createRules());
        $validator->after(fn ($v) => $this->assertParticularRows($v));
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $product = DB::transaction(function () use ($request) {
            $product = WebCleaningAgentProduct::create($this->parentAttributes($request) + [
                'user_id' => (int) $request->input('user_id'),
                'status' => 0,
            ]);

            $this->syncParticulars($product, $request);

            return $product->load(['particulars.particular']);
        });

        VendorProductAdminNotificationService::notify(
            'cleaning_agent',
            VendorProductAdminNotificationService::ACTION_CREATED,
            $product,
            $product->particulars
        );

        return response()->json([
            'status' => true,
            'message' => 'Cleaning agent product saved successfully.',
            'data' => $this->serializeProduct($product),
        ], 200);
    }

    public function update(Request $request)
    {
        $this->normalizeIncomingPayload($request);

        if ($denied = $this->denyIfUserMismatch($request)) {
            return $denied;
        }

        $productId = (int) $request->input('id');
        if ($productId <= 0) {
            return $this->create($request);
        }

        $validator = Validator::make($request->all(), $this->updateRules());
        $validator->after(fn ($v) => $this->assertParticularRows($v));
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $product = WebCleaningAgentProduct::with(['particulars'])->find($productId);
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => 'Cleaning agent product not found.',
            ], 404);
        }

        if ($denied = $this->denyIfNotOwner($request, (int) $product->user_id, 'update')) {
            return $denied;
        }

        DB::transaction(function () use ($request, $product) {
            $product->update($this->parentAttributes($request));
            $this->syncParticulars($product, $request);
        });

        $product->load(['particulars.particular']);

        VendorProductAdminNotificationService::notify(
            'cleaning_agent',
            VendorProductAdminNotificationService::ACTION_UPDATED,
            $product,
            $product->particulars
        );

        return response()->json([
            'status' => true,
            'message' => 'Cleaning agent product updated successfully.',
            'data' => $this->serializeProduct($product),
        ], 200);
    }

    public function listByUser(Request $request, $userId)
    {
        $products = WebCleaningAgentProduct::with(['particulars.particular'])
            ->where('user_id', (int) $userId)
            ->orderByDesc('id')
            ->get()
            ->map(fn (WebCleaningAgentProduct $product) => $this->serializeProduct($product))
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Cleaning agent products fetched successfully.',
            'data' => $products,
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $product = WebCleaningAgentProduct::with(['particulars.particular'])->find((int) $id);
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => 'Cleaning agent product not found.',
            ], 404);
        }

        if ($denied = $this->denyIfNotOwner($request, (int) $product->user_id, 'access')) {
            return $denied;
        }

        return response()->json([
            'status' => true,
            'message' => 'Cleaning agent product fetched successfully.',
            'data' => $this->serializeProduct($product),
        ], 200);
    }

    public function delete(Request $request, $id)
    {
        $product = WebCleaningAgentProduct::find((int) $id);
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => 'Cleaning agent product not found.',
            ], 404);
        }

        if ($denied = $this->denyIfNotOwner($request, (int) $product->user_id, 'delete')) {
            return $denied;
        }

        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Cleaning agent product deleted successfully.',
        ], 200);
    }

    /**
     * @param  array<int>  $ownerIds
     */
    public function verifiedProductsForOwners(array $ownerIds)
    {
        $ownerIds = array_values(array_unique(array_filter(array_map('intval', $ownerIds))));
        if ($ownerIds === []) {
            return WebCleaningAgentProduct::query()->whereRaw('1 = 0')->get();
        }

        return WebCleaningAgentProduct::with(['particulars.particular'])
            ->whereIn('user_id', $ownerIds)
            ->where('status', 1)
            ->whereHas('particulars')
            ->orderByDesc('id')
            ->get();
    }

    public function serializeVendorProduct(WebCleaningAgentProduct $product): array
    {
        return $this->serializeProduct($product);
    }

    /**
     * @return array{ok:bool, activated?:bool, deactivated?:bool, missing_reason?:bool}|false
     */
    public function toggleStatus(int $id, ?string $reason = null)
    {
        $product = WebCleaningAgentProduct::find($id);
        if ($product === null) {
            return false;
        }

        $wasActive = (int) $product->status === 1;
        if ($wasActive) {
            $reason = is_string($reason) ? trim($reason) : '';
            if ($reason === '') {
                return ['ok' => false, 'deactivated' => false, 'missing_reason' => true];
            }

            $product->update(['status' => 0]);
            VendorProductAdminNotificationService::notifyDeactivated(
                'cleaning_agent',
                $product->fresh(),
                $reason
            );

            return ['ok' => true, 'deactivated' => true];
        }

        $product->update(['status' => 1]);
        VendorProductAdminNotificationService::notifyAccepted('cleaning_agent', $product->fresh());

        return ['ok' => true, 'activated' => true];
    }

    public function serializeProduct(WebCleaningAgentProduct $product): array
    {
        $rows = $product->particulars ?? collect();

        $particulars = $rows
            ->filter(fn (CleaningAgentParticularMap $row) => (int) $row->is_other !== 1 && $row->particular_id)
            ->map(fn (CleaningAgentParticularMap $row) => $this->serializeMapRow($row))
            ->values()
            ->all();

        $others = $rows
            ->filter(fn (CleaningAgentParticularMap $row) => (int) $row->is_other === 1 || (! $row->particular_id && $row->particular_name))
            ->map(fn (CleaningAgentParticularMap $row) => $this->serializeMapRow($row))
            ->values()
            ->all();

        return [
            'id' => (int) $product->id,
            'userId' => (int) $product->user_id,
            'container20Ft' => (int) $product->container_20_ft === 1,
            'container40Ft' => (int) $product->container_40_ft === 1,
            'portType' => $product->port_type,
            'icdLocation' => $product->icd_location,
            'portLocation' => $product->port_location,
            'destination' => $product->destination,
            'additionalInformation' => $product->additional_information,
            'status' => (int) $product->status,
            'particulars' => $particulars,
            'others' => $others,
            'updatedAt' => $product->updated_at
                ? $product->updated_at->timezone(config('app.timezone', 'Asia/Kolkata'))->format('d F Y')
                : null,
        ];
    }

    private function serializeMapRow(CleaningAgentParticularMap $row): array
    {
        $masterName = optional($row->particular)->particular;

        return [
            'id' => (int) $row->id,
            'particularId' => $row->particular_id !== null ? (int) $row->particular_id : null,
            'particularName' => $row->particular_name ?: $masterName,
            'rate' => $row->rate !== null ? (string) $row->rate : null,
            'isOther' => (int) $row->is_other === 1,
            'sortOrder' => (int) $row->sort_order,
        ];
    }

    private function parentAttributes(Request $request): array
    {
        return [
            'container_20_ft' => $this->boolFlag($request, ['container_20_ft', 'container20Ft', '20_ft', '20ft']) ? 1 : 0,
            'container_40_ft' => $this->boolFlag($request, ['container_40_ft', 'container40Ft', '40_ft', '40ft']) ? 1 : 0,
            'port_type' => $this->nullableString($request->input('port_type', $request->input('portType'))),
            'icd_location' => $this->nullableString($request->input('icd_location', $request->input('icdLocation'))),
            'port_location' => $this->nullableString($request->input('port_location', $request->input('portLocation'))),
            'destination' => $this->nullableString($request->input('destination')),
            'additional_information' => $this->nullableString(
                $request->input('additional_information', $request->input('additionalInformation'))
            ),
        ];
    }

    private function syncParticulars(WebCleaningAgentProduct $product, Request $request): void
    {
        CleaningAgentParticularMap::query()->where('product_id', $product->id)->delete();

        $sort = 0;
        $particulars = $request->input('particulars', []);
        if (! is_array($particulars)) {
            $particulars = [];
        }

        foreach ($particulars as $row) {
            if (! is_array($row)) {
                continue;
            }

            $particularId = $row['particular_id'] ?? $row['particularId'] ?? null;
            $rate = $row['rate'] ?? $row['price'] ?? null;
            if ($particularId === null || $particularId === '' || $rate === null || $rate === '') {
                continue;
            }

            $masterName = VendorContainerParticular::query()
                ->where('id', (int) $particularId)
                ->value('particular');

            CleaningAgentParticularMap::create([
                'product_id' => $product->id,
                'particular_id' => (int) $particularId,
                'particular_name' => $masterName,
                'rate' => $rate,
                'is_other' => 0,
                'sort_order' => $sort++,
            ]);
        }

        $others = $request->input('others', $request->input('other_particulars', []));
        if (! is_array($others)) {
            $others = [];
        }

        foreach ($others as $row) {
            if (! is_array($row)) {
                continue;
            }

            $name = trim((string) (
                $row['particular_name']
                ?? $row['particularName']
                ?? $row['charge_name']
                ?? $row['chargeName']
                ?? $row['name']
                ?? ''
            ));
            $rate = $row['rate'] ?? $row['price'] ?? null;
            if ($name === '' || $rate === null || $rate === '') {
                continue;
            }

            CleaningAgentParticularMap::create([
                'product_id' => $product->id,
                'particular_id' => null,
                'particular_name' => $name,
                'rate' => $rate,
                'is_other' => 1,
                'sort_order' => $sort++,
            ]);
        }
    }

    private function createRules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'port_type' => ['nullable', 'string', 'max:255'],
            'portType' => ['nullable', 'string', 'max:255'],
            'icd_location' => ['nullable', 'string', 'max:255'],
            'icdLocation' => ['nullable', 'string', 'max:255'],
            'port_location' => ['nullable', 'string', 'max:255'],
            'portLocation' => ['nullable', 'string', 'max:255'],
            'destination' => ['nullable', 'string', 'max:255'],
            'additional_information' => ['nullable', 'string'],
            'additionalInformation' => ['nullable', 'string'],
            'particulars' => ['nullable', 'array'],
            'particulars.*.particular_id' => ['nullable', 'integer', 'exists:vendor_container_particulars,id'],
            'particulars.*.particularId' => ['nullable', 'integer', 'exists:vendor_container_particulars,id'],
            'particulars.*.rate' => ['nullable', 'numeric'],
            'particulars.*.price' => ['nullable', 'numeric'],
            'others' => ['nullable', 'array'],
            'others.*.particular_name' => ['nullable', 'string', 'max:255'],
            'others.*.charge_name' => ['nullable', 'string', 'max:255'],
            'others.*.rate' => ['nullable', 'numeric'],
            'others.*.price' => ['nullable', 'numeric'],
        ];
    }

    private function updateRules(): array
    {
        return array_merge($this->createRules(), [
            'id' => ['required', 'integer', 'exists:web_cleaning_agent_products,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);
    }

    private function assertParticularRows($validator): void
    {
        $data = $validator->getData();
        $particulars = is_array($data['particulars'] ?? null) ? $data['particulars'] : [];
        $others = is_array($data['others'] ?? null)
            ? $data['others']
            : (is_array($data['other_particulars'] ?? null) ? $data['other_particulars'] : []);

        $hasParticular = false;
        foreach ($particulars as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id = $row['particular_id'] ?? $row['particularId'] ?? null;
            $rate = $row['rate'] ?? $row['price'] ?? null;
            if ($id !== null && $id !== '' && $rate !== null && $rate !== '') {
                $hasParticular = true;
                break;
            }
        }

        $hasOther = false;
        foreach ($others as $row) {
            if (! is_array($row)) {
                continue;
            }
            $name = trim((string) (
                $row['particular_name'] ?? $row['particularName'] ?? $row['charge_name'] ?? $row['chargeName'] ?? $row['name'] ?? ''
            ));
            $rate = $row['rate'] ?? $row['price'] ?? null;
            if ($name !== '' && $rate !== null && $rate !== '') {
                $hasOther = true;
                break;
            }
        }

        $container20 = $this->boolFlagFromArray($data, ['container_20_ft', 'container20Ft', '20_ft', '20ft']);
        $container40 = $this->boolFlagFromArray($data, ['container_40_ft', 'container40Ft', '40_ft', '40ft']);
        if (! $container20 && ! $container40) {
            $validator->errors()->add('container', 'Select at least one container size (20 FT or 40 FT).');
        }

        if (! $hasParticular && ! $hasOther) {
            $validator->errors()->add('particulars', 'Add at least one particular rate or an Other charge.');
        }
    }

    private function normalizeIncomingPayload(Request $request): void
    {
        foreach (['payload', 'data', 'body', 'json'] as $wrapper) {
            $wrapped = $request->input($wrapper);
            if (is_array($wrapped)) {
                $request->merge($wrapped);
            }
        }

        if ($request->filled('userId') && ! $request->filled('user_id')) {
            $request->merge(['user_id' => $request->input('userId')]);
        }

        // Allow FE to send containerSizes: ["20_ft","40_ft"]
        $sizes = $request->input('container_sizes', $request->input('containerSizes'));
        if (is_array($sizes)) {
            $normalized = array_map(fn ($s) => strtolower(str_replace([' ', '-'], '_', (string) $s)), $sizes);
            if (in_array('20_ft', $normalized, true) || in_array('20ft', $normalized, true)) {
                $request->merge(['container_20_ft' => 1]);
            }
            if (in_array('40_ft', $normalized, true) || in_array('40ft', $normalized, true)) {
                $request->merge(['container_40_ft' => 1]);
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

    private function denyIfNotOwner(Request $request, int $ownerUserId, string $action)
    {
        $authUser = $request->user();
        if ($authUser && $ownerUserId !== (int) $authUser->id) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden: You are not allowed to '.$action.' this product.',
            ], 403);
        }

        return null;
    }

    private function boolFlag(Request $request, array $keys): bool
    {
        foreach ($keys as $key) {
            if (! $request->exists($key) && $request->input($key) === null) {
                continue;
            }
            $value = $request->input($key);
            if ($value === true || $value === 1 || $value === '1' || $value === 'true' || $value === 'on' || $value === 'yes') {
                return true;
            }
        }

        return false;
    }

    private function boolFlagFromArray(array $data, array $keys): bool
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }
            $value = $data[$key];
            if ($value === true || $value === 1 || $value === '1' || $value === 'true' || $value === 'on' || $value === 'yes') {
                return true;
            }
        }

        return false;
    }

    private function nullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
