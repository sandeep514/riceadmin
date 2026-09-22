<?php

namespace App\Services;

use App\DomesticVendorCity;
use App\DomesticVendorDestination;
use App\DomesticVendorState;
use App\DomesticVendorTruckSize;
use App\WebDomesticFreightProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WebDomesticFreightProductService
{
    public function sync(Request $request)
    {
        if ($denied = $this->denyIfUserMismatch($request)) {
            return $denied;
        }

        $userId = (int) $request->input('user_id', $request->input('userId'));
        if ($userId <= 0) {
            $authUser = $request->user();
            $userId = $authUser ? (int) $authUser->id : 0;
        }

        if ($userId <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'User id is required.',
            ], 422);
        }

        $routes = $request->input('routes');
        if (! is_array($routes)) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => ['routes' => ['Routes must be an array.']],
            ], 422);
        }

        $validator = Validator::make(
            ['routes' => $routes],
            [
                'routes' => ['required', 'array', 'min:1'],
                'routes.*.id' => ['nullable', 'integer'],
                'routes.*.state_id' => ['nullable', 'integer', Rule::exists('domestic_vendor_states', 'id')],
                'routes.*.city_id' => ['required', 'integer', Rule::exists('domestic_vendor_cities', 'id')],
                'routes.*.destination_id' => ['required', 'integer', Rule::exists('domestic_vendor_destinations', 'id')],
                'routes.*.truck_size_id' => ['required', 'integer', Rule::exists('domestic_vendor_truck_sizes', 'id')],
                'routes.*.price' => ['required', 'string', 'max:64', 'regex:/^\d+(\.\d+)?(-\d+(\.\d+)?)?$/'],
                'routes.*.state' => ['nullable', 'string', 'max:255'],
                'routes.*.city' => ['nullable', 'string', 'max:255'],
                'routes.*.destination' => ['nullable', 'string', 'max:255'],
                'routes.*.truck_size' => ['nullable', 'string', 'max:255'],
            ],
            [
                'routes.*.price.regex' => 'Enter a price like 165 or 165-170.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $saved = DB::transaction(function () use ($routes, $userId) {
            $keepIds = [];
            $rows = [];

            foreach ($routes as $route) {
                $attributes = $this->routeAttributes($route);
                $routeId = isset($route['id']) ? (int) $route['id'] : 0;

                if ($routeId > 0) {
                    $existing = WebDomesticFreightProduct::query()
                        ->where('id', $routeId)
                        ->where('user_id', $userId)
                        ->first();

                    if ($existing !== null) {
                        $existing->update($attributes);
                        $keepIds[] = (int) $existing->id;
                        $rows[] = $existing->fresh();
                        continue;
                    }
                }

                $created = WebDomesticFreightProduct::create($attributes + [
                    'user_id' => $userId,
                    'status' => WebDomesticFreightProduct::STATUS_PENDING,
                ]);
                $keepIds[] = (int) $created->id;
                $rows[] = $created;
            }

            $deleteQuery = WebDomesticFreightProduct::query()->where('user_id', $userId);
            if ($keepIds !== []) {
                $deleteQuery->whereNotIn('id', $keepIds);
            }
            $deleteQuery->delete();

            return collect($rows)->sortByDesc('id')->values();
        });

        if ($saved->isNotEmpty()) {
            VendorProductAdminNotificationService::notify(
                'domestic_freight',
                VendorProductAdminNotificationService::ACTION_UPDATED,
                $saved->first(),
                $saved
            );
        }

        return response()->json([
            'status' => true,
            'message' => 'Domestic freight routes saved successfully.',
            'data' => $saved->map(fn (WebDomesticFreightProduct $row) => $this->serialize($row))->values(),
        ], 200);
    }

    public function listByUser(Request $request, $userId)
    {
        $products = WebDomesticFreightProduct::query()
            ->where('user_id', (int) $userId)
            ->orderByDesc('id')
            ->get()
            ->map(fn (WebDomesticFreightProduct $row) => $this->serialize($row))
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Domestic freight routes fetched successfully.',
            'data' => $products,
        ], 200);
    }

    public function delete(Request $request, $id)
    {
        $product = WebDomesticFreightProduct::query()->find((int) $id);
        if ($product === null) {
            return response()->json([
                'status' => false,
                'message' => 'Domestic freight route not found.',
            ], 404);
        }

        if ($denied = $this->denyIfNotOwner($request, (int) $product->user_id, 'delete')) {
            return $denied;
        }

        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Domestic freight route deleted successfully.',
        ], 200);
    }

    /**
     * @param  array<string, mixed>  $route
     * @return array<string, mixed>
     */
    private function routeAttributes(array $route): array
    {
        $cityId = isset($route['city_id']) ? (int) $route['city_id'] : null;
        $stateId = isset($route['state_id']) && $route['state_id'] !== '' ? (int) $route['state_id'] : null;
        $destinationId = isset($route['destination_id']) ? (int) $route['destination_id'] : null;
        $truckSizeId = isset($route['truck_size_id']) ? (int) $route['truck_size_id'] : null;

        $city = $cityId ? DomesticVendorCity::query()->with('state')->find($cityId) : null;
        $state = $stateId
            ? DomesticVendorState::query()->find($stateId)
            : optional($city)->state;
        $destination = $destinationId ? DomesticVendorDestination::query()->find($destinationId) : null;
        $truckSize = $truckSizeId ? DomesticVendorTruckSize::query()->find($truckSizeId) : null;

        $price = preg_replace('/\s+/', '', (string) ($route['price'] ?? ''));

        return [
            'state_id' => $state ? (int) $state->id : ($city?->state_id ? (int) $city->state_id : null),
            'state' => trim((string) ($route['state'] ?? '')) ?: optional($state)->name,
            'city_id' => $cityId,
            'city' => trim((string) ($route['city'] ?? '')) ?: optional($city)->name,
            'destination_id' => $destinationId,
            'destination' => trim((string) ($route['destination'] ?? '')) ?: optional($destination)->name,
            'truck_size_id' => $truckSizeId,
            'truck_size' => trim((string) ($route['truck_size'] ?? ''))
                ?: ($truckSize ? $truckSize->displayLabel() : null),
            'price' => $price,
        ];
    }

    private function serialize(WebDomesticFreightProduct $row): array
    {
        return [
            'id' => (int) $row->id,
            'user_id' => (int) $row->user_id,
            'state_id' => $row->state_id !== null ? (int) $row->state_id : null,
            'state' => $row->state,
            'city_id' => $row->city_id !== null ? (int) $row->city_id : null,
            'city' => $row->city,
            'destination_id' => $row->destination_id !== null ? (int) $row->destination_id : null,
            'destination' => $row->destination,
            'truck_size_id' => $row->truck_size_id !== null ? (int) $row->truck_size_id : null,
            'truck_size' => $row->truck_size,
            'price' => $row->price,
            'status' => (int) $row->status,
            'admin_message' => $row->admin_message,
            'created_at' => optional($row->created_at)->toIso8601String(),
            'updated_at' => optional($row->updated_at)->toIso8601String(),
        ];
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
}
