<?php

namespace App\Services;

use App\VendorContainerSize;
use App\VendorDestinationPort;
use App\VendorForwarderChargeTitle;
use App\VendorIcdLocation;
use App\VendorIndianPort;
use App\VendorPortType;
use App\WebForwarderCharge;
use App\WebForwarderProduct;
use App\WebForwarderProductSize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WebForwarderProductService
{
    public function create(Request $request)
    {
        $this->normalizeIncomingPayload($request);

        if ($denied = $this->denyIfUserMismatch($request)) {
            return $denied;
        }

        $validator = Validator::make($request->all(), $this->createRules());
        $validator->after(fn ($v) => $this->assertProductRows($v, $request));
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $product = DB::transaction(function () use ($request) {
            $product = WebForwarderProduct::create($this->parentAttributes($request) + [
                'user_id' => (int) $request->input('user_id'),
                'status' => 0,
            ]);

            $this->syncContainerSizes($product, $request);
            $this->syncCharges($product, $request);

            return $product->load($this->defaultRelations());
        });

        VendorProductAdminNotificationService::notify(
            'forwarder',
            VendorProductAdminNotificationService::ACTION_CREATED,
            $product,
            $product->charges
        );

        return response()->json([
            'status' => true,
            'message' => 'Forwarder product saved successfully.',
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
        $validator->after(fn ($v) => $this->assertProductRows($v, $request));
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $product = WebForwarderProduct::with(['charges', 'containerSizes'])->find($productId);
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => 'Forwarder product not found.',
            ], 404);
        }

        if ($denied = $this->denyIfNotOwner($request, (int) $product->user_id, 'update')) {
            return $denied;
        }

        DB::transaction(function () use ($request, $product) {
            $product->update($this->parentAttributes($request));
            $this->syncContainerSizes($product, $request);
            $this->syncCharges($product, $request);
        });

        $product->load($this->defaultRelations());

        VendorProductAdminNotificationService::notify(
            'forwarder',
            VendorProductAdminNotificationService::ACTION_UPDATED,
            $product,
            $product->charges
        );

        return response()->json([
            'status' => true,
            'message' => 'Forwarder product updated successfully.',
            'data' => $this->serializeProduct($product),
        ], 200);
    }

    public function listByUser(Request $request, $userId)
    {
        $products = WebForwarderProduct::with($this->defaultRelations())
            ->where('user_id', (int) $userId)
            ->orderByDesc('id')
            ->get()
            ->map(fn (WebForwarderProduct $product) => $this->serializeProduct($product))
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Forwarder products fetched successfully.',
            'data' => $products,
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $product = WebForwarderProduct::with($this->defaultRelations())->find((int) $id);
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => 'Forwarder product not found.',
            ], 404);
        }

        if ($denied = $this->denyIfNotOwner($request, (int) $product->user_id, 'access')) {
            return $denied;
        }

        return response()->json([
            'status' => true,
            'message' => 'Forwarder product fetched successfully.',
            'data' => $this->serializeProduct($product),
        ], 200);
    }

    public function delete(Request $request, $id)
    {
        $product = WebForwarderProduct::find((int) $id);
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => 'Forwarder product not found.',
            ], 404);
        }

        if ($denied = $this->denyIfNotOwner($request, (int) $product->user_id, 'delete')) {
            return $denied;
        }

        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Forwarder product deleted successfully.',
        ], 200);
    }

    /**
     * @param  array<int>  $ownerIds
     */
    public function verifiedProductsForOwners(array $ownerIds)
    {
        $ownerIds = array_values(array_unique(array_filter(array_map('intval', $ownerIds))));
        if ($ownerIds === []) {
            return WebForwarderProduct::query()->whereRaw('1 = 0')->get();
        }

        return WebForwarderProduct::with($this->defaultRelations())
            ->whereIn('user_id', $ownerIds)
            ->where('status', 1)
            ->whereHas('charges')
            ->orderByDesc('id')
            ->get();
    }

    public function serializeVendorProduct(WebForwarderProduct $product): array
    {
        return $this->serializeProduct($product);
    }

    /**
     * @return array{ok:bool, activated?:bool, deactivated?:bool, missing_reason?:bool}|false
     */
    public function toggleStatus(int $id, ?string $reason = null)
    {
        $product = WebForwarderProduct::find($id);
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
                'forwarder',
                $product->fresh(),
                $reason
            );

            return ['ok' => true, 'deactivated' => true];
        }

        $product->update(['status' => 1, 'admin_message' => null]);
        VendorProductAdminNotificationService::notifyAccepted('forwarder', $product->fresh());

        return ['ok' => true, 'activated' => true];
    }

    public function serializeProduct(WebForwarderProduct $product): array
    {
        $sizes = ($product->containerSizes ?? collect())->map(function (WebForwarderProductSize $row) {
            $size = $row->size !== null
                ? (int) $row->size
                : (optional($row->containerSizeRel)->size !== null ? (int) $row->containerSizeRel->size : null);

            return [
                'id' => $row->container_size_id !== null ? (int) $row->container_size_id : null,
                'size' => $size,
                'label' => optional($row->containerSizeRel)->label
                    ?: ($size ? $size.' FT' : null),
            ];
        })->values()->all();

        $charges = ($product->charges ?? collect())
            ->map(fn (WebForwarderCharge $row) => $this->serializeCharge($row))
            ->values()
            ->all();

        $totals = $this->totalsFromCharges($product->charges ?? collect());
        $destinationPort = $product->destinationPortRel;

        return [
            'id' => (int) $product->id,
            'userId' => (int) $product->user_id,
            'portTypeId' => $product->port_type_id !== null ? (int) $product->port_type_id : null,
            'portType' => optional($product->portTypeRel)->name ?: $product->port_type,
            'icdLocationId' => $product->icd_location_id !== null ? (int) $product->icd_location_id : null,
            'icdLocation' => optional($product->icdLocationRel)->name ?: $product->icd_location,
            'indianPortId' => $product->indian_port_id !== null ? (int) $product->indian_port_id : null,
            'indianPort' => optional($product->indianPortRel)->name ?: $product->port_location,
            'portLocation' => optional($product->indianPortRel)->name ?: $product->port_location,
            'regionId' => $product->region_id !== null
                ? (int) $product->region_id
                : (optional($destinationPort)->region_id !== null ? (int) $destinationPort->region_id : null),
            'region' => optional($product->regionRel)->name
                ?: optional(optional($destinationPort)->region)->name,
            'countryId' => $product->country_id !== null
                ? (int) $product->country_id
                : (optional($destinationPort)->country_id !== null ? (int) $destinationPort->country_id : null),
            'country' => optional($product->countryRel)->name
                ?: optional(optional($destinationPort)->country)->name,
            'destinationPortId' => $product->destination_port_id !== null ? (int) $product->destination_port_id : null,
            'destination' => optional($destinationPort)->name ?: $product->destination,
            'destinationPort' => optional($destinationPort)->name ?: $product->destination,
            'containerSizeIds' => array_values(array_filter(array_map(
                fn ($row) => $row['id'] ?? null,
                $sizes
            ))),
            'containerSizes' => $sizes,
            'additionalInformation' => $product->additional_information,
            'status' => (int) $product->status,
            'charges' => $charges,
            'particulars' => $charges,
            'totalUsd' => $totals['usd'],
            'totalInr' => $totals['inr'],
            'updatedAt' => $product->updated_at
                ? $product->updated_at->timezone(config('app.timezone', 'Asia/Kolkata'))->format('d F Y')
                : null,
        ];
    }

    /**
     * @return list<string>
     */
    private function defaultRelations(): array
    {
        return [
            'charges.titleRel',
            'containerSizes.containerSizeRel',
            'portTypeRel',
            'icdLocationRel',
            'indianPortRel',
            'regionRel',
            'countryRel',
            'destinationPortRel.region',
            'destinationPortRel.country',
        ];
    }

    private function serializeCharge(WebForwarderCharge $row): array
    {
        $title = $row->title ?: optional($row->titleRel)->name;
        $currency = strtoupper(trim((string) ($row->currency ?: WebForwarderCharge::CURRENCY_INR)));
        $exchange = $currency === WebForwarderCharge::CURRENCY_INR
            ? ($row->exchange_rate ?: '-')
            : $row->exchange_rate;

        return [
            'id' => (int) $row->id,
            'titleId' => $row->title_id !== null ? (int) $row->title_id : null,
            'title' => $title,
            'currency' => $currency,
            'charges' => $row->charges !== null ? (string) $row->charges : null,
            'exchangeRate' => $exchange,
            'exc' => $exchange,
            'inr' => $row->inr_amount !== null ? (string) $row->inr_amount : null,
            'inrAmount' => $row->inr_amount !== null ? (string) $row->inr_amount : null,
            'remarks' => $row->remarks,
            'isOther' => (int) $row->is_other === 1,
            'sortOrder' => (int) $row->sort_order,
        ];
    }

    private function parentAttributes(Request $request): array
    {
        $portTypeId = $this->nullableInt($request->input('port_type_id', $request->input('portTypeId')));
        $icdLocationId = $this->nullableInt($request->input('icd_location_id', $request->input('icdLocationId')));
        $indianPortId = $this->nullableInt($request->input(
            'indian_port_id',
            $request->input('indianPortId', $request->input('port_location_id', $request->input('portLocationId')))
        ));
        $regionId = $this->nullableInt($request->input('region_id', $request->input('regionId')));
        $countryId = $this->nullableInt($request->input('country_id', $request->input('countryId')));
        $destinationPortId = $this->nullableInt($request->input(
            'destination_port_id',
            $request->input('destinationPortId', $request->input('destination_id', $request->input('destinationId')))
        ));

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

        if ($destinationPortId) {
            $destinationPort = VendorDestinationPort::query()->find($destinationPortId);
            if ($destinationPort) {
                if (! $regionId && $destinationPort->region_id) {
                    $regionId = (int) $destinationPort->region_id;
                }
                if (! $countryId && $destinationPort->country_id) {
                    $countryId = (int) $destinationPort->country_id;
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
            'port_type_id' => $portTypeId,
            'port_type' => $portTypeName,
            'icd_location_id' => $icdLocationId,
            'icd_location' => $icdName,
            'indian_port_id' => $indianPortId,
            'port_location' => $indianPortName,
            'region_id' => $regionId,
            'country_id' => $countryId,
            'destination_port_id' => $destinationPortId,
            'destination' => $destinationName,
            'additional_information' => $this->nullableString(
                $request->input('additional_information', $request->input('additionalInformation'))
            ),
        ];
    }

    private function syncContainerSizes(WebForwarderProduct $product, Request $request): void
    {
        WebForwarderProductSize::query()->where('product_id', $product->id)->delete();

        foreach ($this->resolvedContainerSizes($request) as $row) {
            WebForwarderProductSize::create([
                'product_id' => $product->id,
                'container_size_id' => $row['id'],
                'size' => $row['size'],
            ]);
        }
    }

    private function syncCharges(WebForwarderProduct $product, Request $request): void
    {
        WebForwarderCharge::query()->where('product_id', $product->id)->delete();

        $sort = 0;
        foreach ($this->chargeRowsFromRequest($request) as $row) {
            $normalized = $this->normalizeChargeRow($row);
            if ($normalized === null) {
                continue;
            }

            WebForwarderCharge::create([
                'product_id' => $product->id,
                'title_id' => $normalized['title_id'],
                'title' => $normalized['title'],
                'currency' => $normalized['currency'],
                'charges' => $normalized['charges'],
                'exchange_rate' => $normalized['exchange_rate'],
                'inr_amount' => $normalized['inr_amount'],
                'remarks' => $normalized['remarks'],
                'is_other' => $normalized['is_other'],
                'sort_order' => $sort++,
            ]);
        }
    }

    /**
     * @return list<array{id:int, size:int}>
     */
    private function resolvedContainerSizes(Request $request): array
    {
        $resolved = [];

        $rawIds = $request->input('container_size_ids', $request->input('containerSizeIds', []));
        if (! is_array($rawIds)) {
            $rawIds = $rawIds !== null && $rawIds !== '' ? [$rawIds] : [];
        }
        foreach ($rawIds as $id) {
            $sizeId = $this->nullableInt($id);
            if (! $sizeId) {
                continue;
            }
            $sizeRow = VendorContainerSize::query()->find($sizeId);
            if (! $sizeRow) {
                continue;
            }
            $resolved[$sizeId] = [
                'id' => $sizeId,
                'size' => (int) $sizeRow->size,
            ];
        }

        $rawSizes = $request->input('container_sizes', $request->input('containerSizes', []));
        if (! is_array($rawSizes)) {
            $rawSizes = $rawSizes !== null && $rawSizes !== '' ? [$rawSizes] : [];
        }
        foreach ($rawSizes as $value) {
            $matched = $this->matchContainerSize($value);
            if ($matched) {
                $resolved[$matched['id']] = $matched;
            }
        }

        foreach ([20, 40] as $ft) {
            $flag = $request->input('container_'.$ft.'_ft', $request->input('container'.$ft.'Ft'));
            if ($this->isTruthy($flag)) {
                $matched = $this->matchContainerSize($ft);
                if ($matched) {
                    $resolved[$matched['id']] = $matched;
                }
            }
        }

        return array_values($resolved);
    }

    /**
     * @return array{id:int, size:int}|null
     */
    private function matchContainerSize($value): ?array
    {
        $size = $this->normalizeContainerSizeValue($value);
        if ($size === null) {
            return null;
        }

        $row = VendorContainerSize::query()
            ->where('size', $size)
            ->where('status', VendorContainerSize::STATUS_ACTIVE)
            ->first();
        if (! $row) {
            $row = VendorContainerSize::query()->where('size', $size)->first();
        }
        if (! $row) {
            return null;
        }

        return [
            'id' => (int) $row->id,
            'size' => (int) $row->size,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function chargeRowsFromRequest(Request $request): array
    {
        $charges = $request->input('charges', $request->input('particulars', []));
        if (! is_array($charges)) {
            $charges = [];
        }

        $others = $request->input('others', $request->input('other_charges', []));
        if (is_array($others)) {
            foreach ($others as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $row['is_other'] = 1;
                $charges[] = $row;
            }
        }

        return array_values(array_filter($charges, 'is_array'));
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{title_id:?int, title:?string, currency:string, charges:?string, exchange_rate:?string, inr_amount:?string, remarks:?string, is_other:int}|null
     */
    private function normalizeChargeRow(array $row): ?array
    {
        $titleId = $this->nullableInt($row['title_id'] ?? $row['titleId'] ?? null);
        $title = $this->nullableString(
            $row['title']
            ?? $row['name']
            ?? $row['particular_name']
            ?? $row['particularName']
            ?? $row['charge_name']
            ?? $row['chargeName']
            ?? null
        );
        $isOther = $this->isTruthy($row['is_other'] ?? $row['isOther'] ?? 0) ? 1 : 0;

        if ($titleId) {
            $master = VendorForwarderChargeTitle::query()->find($titleId);
            if ($master) {
                $title = $master->name;
            } else {
                $titleId = null;
            }
        } elseif ($title) {
            $matchedId = VendorForwarderChargeTitle::query()
                ->whereRaw('LOWER(name) = ?', [strtolower($title)])
                ->where('status', VendorForwarderChargeTitle::STATUS_ACTIVE)
                ->value('id');
            if ($matchedId) {
                $titleId = (int) $matchedId;
                $title = VendorForwarderChargeTitle::query()->where('id', $titleId)->value('name');
            } else {
                $isOther = 1;
            }
        }

        $charges = $this->nullableString(
            $row['charges'] ?? $row['rate'] ?? $row['price'] ?? $row['value'] ?? $row['amount'] ?? null
        );
        if ($title === null && $titleId === null && $charges === null) {
            return null;
        }

        $currency = $this->normalizeCurrency($row['currency'] ?? $row['curr'] ?? WebForwarderCharge::CURRENCY_INR);
        $exchange = $this->nullableString(
            $row['exchange_rate'] ?? $row['exchangeRate'] ?? $row['exc'] ?? $row['exchange'] ?? null
        );
        if ($exchange === '-') {
            $exchange = null;
        }
        $inr = $this->nullableString(
            $row['inr'] ?? $row['inr_amount'] ?? $row['inrAmount'] ?? $row['inr_value'] ?? null
        );
        $remarks = $this->nullableString($row['remarks'] ?? $row['remark'] ?? $row['note'] ?? null);

        if ($currency === WebForwarderCharge::CURRENCY_INR) {
            $exchange = $exchange ?: '-';
            if ($inr === null && $charges !== null) {
                $inr = $charges;
            }
        } elseif ($inr === null && is_numeric($charges) && is_numeric($exchange)) {
            $inr = (string) round(((float) $charges) * ((float) $exchange), 2);
        }

        return [
            'title_id' => $titleId,
            'title' => $title,
            'currency' => $currency,
            'charges' => $charges,
            'exchange_rate' => $exchange,
            'inr_amount' => $inr,
            'remarks' => $remarks,
            'is_other' => $isOther,
        ];
    }

    private function createRules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
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
            'region_id' => ['nullable', 'integer', 'exists:vendor_destination_regions,id'],
            'regionId' => ['nullable', 'integer', 'exists:vendor_destination_regions,id'],
            'country_id' => ['nullable', 'integer', 'exists:vendor_destination_countries,id'],
            'countryId' => ['nullable', 'integer', 'exists:vendor_destination_countries,id'],
            'destination_port_id' => ['nullable', 'integer', 'exists:vendor_destination_ports,id'],
            'destinationPortId' => ['nullable', 'integer', 'exists:vendor_destination_ports,id'],
            'destination_id' => ['nullable', 'integer', 'exists:vendor_destination_ports,id'],
            'destinationId' => ['nullable', 'integer', 'exists:vendor_destination_ports,id'],
            'additional_information' => ['nullable', 'string'],
            'additionalInformation' => ['nullable', 'string'],
            'container_size_ids' => ['nullable', 'array'],
            'container_size_ids.*' => ['nullable', 'integer', 'exists:vendor_container_sizes,id'],
            'containerSizeIds' => ['nullable', 'array'],
            'containerSizeIds.*' => ['nullable', 'integer', 'exists:vendor_container_sizes,id'],
            'container_sizes' => ['nullable', 'array'],
            'containerSizes' => ['nullable', 'array'],
            'charges' => ['nullable', 'array'],
            'particulars' => ['nullable', 'array'],
            'others' => ['nullable', 'array'],
        ];
    }

    private function updateRules(): array
    {
        return array_merge($this->createRules(), [
            'id' => ['required', 'integer', 'exists:web_forwarder_products,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);
    }

    private function assertProductRows($validator, Request $request): void
    {
        if ($this->resolvedContainerSizes($request) === []) {
            $validator->errors()->add('containerSizeIds', 'Select at least one container type (20 FT or 40 FT).');
        }

        $hasCharge = false;
        foreach ($this->chargeRowsFromRequest($request) as $row) {
            $normalized = $this->normalizeChargeRow($row);
            if ($normalized === null) {
                continue;
            }
            if ($normalized['title'] === null && $normalized['title_id'] === null) {
                continue;
            }
            if ($normalized['charges'] === null || $normalized['charges'] === '') {
                continue;
            }
            $hasCharge = true;
            break;
        }

        if (! $hasCharge) {
            $validator->errors()->add('charges', 'Add at least one charge with a title and amount.');
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

    private function totalsFromCharges($charges): array
    {
        $usd = 0.0;
        $inr = 0.0;
        foreach ($charges as $row) {
            $currency = strtoupper(trim((string) ($row->currency ?? '')));
            if ($currency === WebForwarderCharge::CURRENCY_USD && is_numeric($row->charges)) {
                $usd += (float) $row->charges;
            }
            if (is_numeric($row->inr_amount)) {
                $inr += (float) $row->inr_amount;
            } elseif ($currency === WebForwarderCharge::CURRENCY_INR && is_numeric($row->charges)) {
                $inr += (float) $row->charges;
            }
        }

        return [
            'usd' => $this->formatMoney($usd),
            'inr' => $this->formatMoney($inr),
        ];
    }

    private function formatMoney(float $value): string
    {
        if (abs($value - round($value)) < 0.001) {
            return (string) (int) round($value);
        }

        return number_format($value, 2, '.', '');
    }

    private function normalizeCurrency($value): string
    {
        $currency = strtoupper(trim((string) $value));

        return $currency === WebForwarderCharge::CURRENCY_USD
            ? WebForwarderCharge::CURRENCY_USD
            : WebForwarderCharge::CURRENCY_INR;
    }

    private function normalizeContainerSizeValue($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_array($value)) {
            $id = $this->nullableInt($value['id'] ?? $value['container_size_id'] ?? $value['containerSizeId'] ?? null);
            if ($id) {
                $size = VendorContainerSize::query()->where('id', $id)->value('size');

                return $size !== null ? (int) $size : null;
            }
            $value = $value['size'] ?? $value['label'] ?? null;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }

        $normalized = strtolower(trim((string) $value));
        $normalized = str_replace([' ', '-'], '_', $normalized);
        $legacy = match ($normalized) {
            '20', '20ft', '20_ft', '20feet', '20_feet' => 20,
            '40', '40ft', '40_ft', '40feet', '40_feet' => 40,
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

    private function isTruthy($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on', 'y'], true);
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
