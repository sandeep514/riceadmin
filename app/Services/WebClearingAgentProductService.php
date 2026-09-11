<?php

namespace App\Services;

use App\ClearingAgentParticularMap;
use App\VendorContainerParticular;
use App\VendorContainerSize;
use App\VendorDestinationPort;
use App\VendorIcdLocation;
use App\VendorIndianPort;
use App\VendorPortType;
use App\WebClearingAgentProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WebClearingAgentProductService
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
            $product = WebClearingAgentProduct::create($this->parentAttributes($request) + [
                'user_id' => (int) $request->input('user_id'),
                'status' => 0,
            ]);

            $this->syncParticulars($product, $request);

            return $product->load([
                'particulars.particular',
                'containerSizeRel',
                'portTypeRel',
                'icdLocationRel',
                'indianPortRel',
                'destinationPortRel',
            ]);
        });

        VendorProductAdminNotificationService::notify(
            'clearing_agent',
            VendorProductAdminNotificationService::ACTION_CREATED,
            $product,
            $product->particulars
        );

        return response()->json([
            'status' => true,
            'message' => 'Clearing agent product saved successfully.',
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

        $product = WebClearingAgentProduct::with(['particulars'])->find($productId);
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => 'Clearing agent product not found.',
            ], 404);
        }

        if ($denied = $this->denyIfNotOwner($request, (int) $product->user_id, 'update')) {
            return $denied;
        }

        DB::transaction(function () use ($request, $product) {
            $product->update($this->parentAttributes($request));
            $this->syncParticulars($product, $request);
        });

        $product->load([
            'particulars.particular',
            'containerSizeRel',
            'portTypeRel',
            'icdLocationRel',
            'indianPortRel',
            'destinationPortRel',
        ]);

        VendorProductAdminNotificationService::notify(
            'clearing_agent',
            VendorProductAdminNotificationService::ACTION_UPDATED,
            $product,
            $product->particulars
        );

        return response()->json([
            'status' => true,
            'message' => 'Clearing agent product updated successfully.',
            'data' => $this->serializeProduct($product),
        ], 200);
    }

    public function listByUser(Request $request, $userId)
    {
        $products = WebClearingAgentProduct::with([
            'particulars.particular',
            'containerSizeRel',
            'portTypeRel',
            'icdLocationRel',
            'indianPortRel',
            'destinationPortRel',
        ])
            ->where('user_id', (int) $userId)
            ->orderByDesc('id')
            ->get()
            ->map(fn (WebClearingAgentProduct $product) => $this->serializeProduct($product))
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Clearing agent products fetched successfully.',
            'data' => $products,
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $product = WebClearingAgentProduct::with([
            'particulars.particular',
            'containerSizeRel',
            'portTypeRel',
            'icdLocationRel',
            'indianPortRel',
            'destinationPortRel',
        ])->find((int) $id);
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => 'Clearing agent product not found.',
            ], 404);
        }

        if ($denied = $this->denyIfNotOwner($request, (int) $product->user_id, 'access')) {
            return $denied;
        }

        return response()->json([
            'status' => true,
            'message' => 'Clearing agent product fetched successfully.',
            'data' => $this->serializeProduct($product),
        ], 200);
    }

    public function delete(Request $request, $id)
    {
        $product = WebClearingAgentProduct::find((int) $id);
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => 'Clearing agent product not found.',
            ], 404);
        }

        if ($denied = $this->denyIfNotOwner($request, (int) $product->user_id, 'delete')) {
            return $denied;
        }

        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Clearing agent product deleted successfully.',
        ], 200);
    }

    /**
     * @param  array<int>  $ownerIds
     */
    public function verifiedProductsForOwners(array $ownerIds)
    {
        $ownerIds = array_values(array_unique(array_filter(array_map('intval', $ownerIds))));
        if ($ownerIds === []) {
            return WebClearingAgentProduct::query()->whereRaw('1 = 0')->get();
        }

        return WebClearingAgentProduct::with([
            'particulars.particular',
            'icdLocationRel',
            'indianPortRel',
            'destinationPortRel',
        ])
            ->whereIn('user_id', $ownerIds)
            ->where('status', 1)
            ->whereHas('particulars')
            ->orderByDesc('id')
            ->get();
    }

    public function serializeVendorProduct(WebClearingAgentProduct $product): array
    {
        return $this->serializeProduct($product);
    }

    /**
     * @return array{ok:bool, activated?:bool, deactivated?:bool, missing_reason?:bool}|false
     */
    public function toggleStatus(int $id, ?string $reason = null)
    {
        $product = WebClearingAgentProduct::find($id);
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
                'clearing_agent',
                $product->fresh(),
                $reason
            );

            return ['ok' => true, 'deactivated' => true];
        }

        $product->update(['status' => 1]);
        VendorProductAdminNotificationService::notifyAccepted('clearing_agent', $product->fresh());

        return ['ok' => true, 'activated' => true];
    }

    public function serializeProduct(WebClearingAgentProduct $product): array
    {
        $rows = $product->particulars ?? collect();

        $particulars = $rows
            ->filter(fn (ClearingAgentParticularMap $row) => (int) $row->is_other !== 1 && $row->particular_id)
            ->map(fn (ClearingAgentParticularMap $row) => $this->serializeMapRow($row))
            ->values()
            ->all();

        $others = $rows
            ->filter(fn (ClearingAgentParticularMap $row) => (int) $row->is_other === 1 || (! $row->particular_id && $row->particular_name))
            ->map(fn (ClearingAgentParticularMap $row) => $this->serializeMapRow($row))
            ->values()
            ->all();

        return [
            'id' => (int) $product->id,
            'userId' => (int) $product->user_id,
            'containerSizeId' => $product->container_size_id !== null ? (int) $product->container_size_id : null,
            'containerSize' => $product->container_size !== null
                ? (int) $product->container_size
                : (optional($product->containerSizeRel)->size !== null ? (int) $product->containerSizeRel->size : null),
            'containerSizeLabel' => optional($product->containerSizeRel)->label
                ?: ($product->container_size ? $product->container_size.' FT' : null),
            'portTypeId' => $product->port_type_id !== null ? (int) $product->port_type_id : null,
            'portType' => optional($product->portTypeRel)->name ?: $product->port_type,
            'icdLocationId' => $product->icd_location_id !== null ? (int) $product->icd_location_id : null,
            'icdLocation' => optional($product->icdLocationRel)->name ?: $product->icd_location,
            'indianPortId' => $product->indian_port_id !== null ? (int) $product->indian_port_id : null,
            'indianPort' => optional($product->indianPortRel)->name ?: $product->port_location,
            'portLocation' => optional($product->indianPortRel)->name ?: $product->port_location,
            'destinationPortId' => $product->destination_port_id !== null ? (int) $product->destination_port_id : null,
            'destination' => optional($product->destinationPortRel)->name ?: $product->destination,
            'additionalInformation' => $product->additional_information,
            'status' => (int) $product->status,
            'particulars' => $particulars,
            'others' => $others,
            'updatedAt' => $product->updated_at
                ? $product->updated_at->timezone(config('app.timezone', 'Asia/Kolkata'))->format('d F Y')
                : null,
        ];
    }

    private function serializeMapRow(ClearingAgentParticularMap $row): array
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
        $containerSizeId = $this->nullableInt($request->input('container_size_id', $request->input('containerSizeId')));
        $portTypeId = $this->nullableInt($request->input('port_type_id', $request->input('portTypeId')));
        $icdLocationId = $this->nullableInt($request->input('icd_location_id', $request->input('icdLocationId')));
        $indianPortId = $this->nullableInt($request->input(
            'indian_port_id',
            $request->input('indianPortId', $request->input('port_location_id', $request->input('portLocationId')))
        ));
        $destinationPortId = $this->nullableInt($request->input(
            'destination_port_id',
            $request->input('destinationPortId', $request->input('destination_id', $request->input('destinationId')))
        ));

        $resolvedSize = $this->resolveContainerSize($request);
        if ($containerSizeId) {
            $sizeRow = VendorContainerSize::query()->find($containerSizeId);
            if ($sizeRow) {
                $resolvedSize = (int) $sizeRow->size;
            }
        } elseif ($resolvedSize !== null) {
            $matchedId = VendorContainerSize::query()
                ->where('size', $resolvedSize)
                ->where('status', VendorContainerSize::STATUS_ACTIVE)
                ->value('id');
            if ($matchedId) {
                $containerSizeId = (int) $matchedId;
            }
        }

        $portTypeName = null;
        if ($portTypeId) {
            $portTypeName = VendorPortType::query()->where('id', $portTypeId)->value('name');
        } else {
            $portTypeName = $this->nullableString($request->input('port_type', $request->input('portType')));
            if ($portTypeName) {
                $matchedPortTypeId = VendorPortType::query()
                    ->whereRaw('LOWER(name) = ?', [strtolower($portTypeName)])
                    ->where('status', VendorPortType::STATUS_ACTIVE)
                    ->value('id');
                if ($matchedPortTypeId) {
                    $portTypeId = (int) $matchedPortTypeId;
                    $portTypeName = VendorPortType::query()->where('id', $portTypeId)->value('name');
                }
            }
        }

        $icdName = $icdLocationId
            ? VendorIcdLocation::query()->where('id', $icdLocationId)->value('name')
            : null;
        $indianPortName = $indianPortId
            ? VendorIndianPort::query()->where('id', $indianPortId)->value('name')
            : null;
        $destinationName = $destinationPortId
            ? VendorDestinationPort::query()->where('id', $destinationPortId)->value('name')
            : null;

        return [
            'container_size_id' => $containerSizeId,
            'container_size' => $resolvedSize,
            'port_type_id' => $portTypeId,
            'port_type' => $portTypeName,
            'icd_location_id' => $icdLocationId,
            'icd_location' => $icdName,
            'indian_port_id' => $indianPortId,
            'port_location' => $indianPortName,
            'destination_port_id' => $destinationPortId,
            'destination' => $destinationName,
            'additional_information' => $this->nullableString(
                $request->input('additional_information', $request->input('additionalInformation'))
            ),
        ];
    }

    private function syncParticulars(WebClearingAgentProduct $product, Request $request): void
    {
        ClearingAgentParticularMap::query()->where('product_id', $product->id)->delete();

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

            ClearingAgentParticularMap::create([
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

            ClearingAgentParticularMap::create([
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
            'container_size_id' => ['nullable', 'integer', 'exists:vendor_container_sizes,id'],
            'containerSizeId' => ['nullable', 'integer', 'exists:vendor_container_sizes,id'],
            'container_size' => ['nullable', 'integer'],
            'containerSize' => ['nullable', 'integer'],
            'port_type_id' => ['nullable', 'integer', 'exists:vendor_port_types,id'],
            'portTypeId' => ['nullable', 'integer', 'exists:vendor_port_types,id'],
            'port_type' => ['nullable', 'string', 'max:255'],
            'portType' => ['nullable', 'string', 'max:255'],
            'icd_location_id' => ['nullable', 'integer', 'exists:vendor_icd_locations,id'],
            'icdLocationId' => ['nullable', 'integer', 'exists:vendor_icd_locations,id'],
            'indian_port_id' => ['nullable', 'integer', 'exists:vendor_indian_ports,id'],
            'indianPortId' => ['nullable', 'integer', 'exists:vendor_indian_ports,id'],
            'port_location_id' => ['nullable', 'integer', 'exists:vendor_indian_ports,id'],
            'portLocationId' => ['nullable', 'integer', 'exists:vendor_indian_ports,id'],
            'destination_port_id' => ['nullable', 'integer', 'exists:vendor_destination_ports,id'],
            'destinationPortId' => ['nullable', 'integer', 'exists:vendor_destination_ports,id'],
            'destination_id' => ['nullable', 'integer', 'exists:vendor_destination_ports,id'],
            'destinationId' => ['nullable', 'integer', 'exists:vendor_destination_ports,id'],
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
            'id' => ['required', 'integer', 'exists:web_clearing_agent_products,id'],
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

        $containerSizeId = $data['container_size_id'] ?? $data['containerSizeId'] ?? null;
        $containerSize = null;
        if ($containerSizeId !== null && $containerSizeId !== '') {
            $containerSize = VendorContainerSize::query()
                ->where('id', (int) $containerSizeId)
                ->where('status', VendorContainerSize::STATUS_ACTIVE)
                ->value('size');
        }
        if ($containerSize === null) {
            $containerSize = $this->normalizeContainerSizeValue(
                $data['container_size'] ?? $data['containerSize'] ?? $data['container_40_ft'] ?? $data['container40Ft'] ?? null
            );
        }
        if ($containerSize === null) {
            $validator->errors()->add('container_size', 'Select a valid container size.');
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

        $size = $this->resolveContainerSize($request);
        if ($size !== null) {
            $request->merge([
                'container_size' => $size,
                'containerSize' => $size,
            ]);
        }

        $containerSizeId = $this->nullableInt($request->input('container_size_id', $request->input('containerSizeId')));
        if ($containerSizeId && ! $request->filled('container_size')) {
            $sizeFromId = VendorContainerSize::query()->where('id', $containerSizeId)->value('size');
            if ($sizeFromId !== null) {
                $request->merge([
                    'container_size' => (int) $sizeFromId,
                    'containerSize' => (int) $sizeFromId,
                ]);
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

    private function resolveContainerSize(Request $request): ?int
    {
        $sizeId = $this->nullableInt($request->input('container_size_id', $request->input('containerSizeId')));
        if ($sizeId) {
            $size = VendorContainerSize::query()->where('id', $sizeId)->value('size');
            if ($size !== null) {
                return (int) $size;
            }
        }

        $raw = $request->input(
            'container_size',
            $request->input(
                'containerSize',
                $request->input('container_40_ft', $request->input('container40Ft'))
            )
        );

        // Legacy boolean container_40_ft=true → 40
        if ($raw === true || $raw === 1 || $raw === '1' || $raw === 'true' || $raw === 'on' || $raw === 'yes') {
            if ($request->exists('container_40_ft') || $request->exists('container40Ft')) {
                return 40;
            }
        }

        return $this->normalizeContainerSizeValue($raw);
    }

    private function normalizeContainerSizeValue($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $size = (int) $value;
            $exists = VendorContainerSize::query()
                ->where('size', $size)
                ->where('status', VendorContainerSize::STATUS_ACTIVE)
                ->exists();

            if ($exists) {
                return $size;
            }

            // Fallback for pre-migration hard-coded sizes
            return in_array($size, [40, 50], true) ? $size : null;
        }

        $normalized = strtolower(trim((string) $value));
        $normalized = str_replace([' ', '-'], '_', $normalized);

        $legacy = match ($normalized) {
            '40', '40ft', '40_ft', '40feet', '40_feet' => 40,
            '50', '50ft', '50_ft', '50feet', '50_feet' => 50,
            default => null,
        };
        if ($legacy !== null) {
            return $legacy;
        }

        $byLabel = VendorContainerSize::query()
            ->where('status', VendorContainerSize::STATUS_ACTIVE)
            ->whereRaw('LOWER(label) = ?', [strtolower(trim((string) $value))])
            ->value('size');

        return $byLabel !== null ? (int) $byLabel : null;
    }

    private function nullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function nullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $id = (int) $value;

        return $id > 0 ? $id : null;
    }
}
