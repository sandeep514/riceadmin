<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BrandAvailability extends Model
{
    protected $table = "brand_availability";
    protected $fillable = ['brand_id', 'state_id', 'city_id', 'status'];

    public function state_rel()
    {
        return $this->belongsTo(WebStates::class , 'state_id', 'id');
    }

    public function city_rel()
    {
        return $this->belongsTo(WebCities::class , 'city_id', 'id');
    }

    /**
     * States and cities where a brand is available, grouped for API responses.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function groupedForBrand(int $brandId, bool $requireActiveWebBrand = false): array
    {
        if ($brandId < 1) {
            return [];
        }

        $query = static::query()
            ->where('brand_id', $brandId)
            ->where('status', 1);

        if ($requireActiveWebBrand) {
            $query->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('web_brands')
                    ->whereColumn('web_brands.id', 'brand_availability.brand_id')
                    ->where('web_brands.status', 1);
            });
        }

        $rows = $query->with([
            'state_rel:id,state_name,state_code,order_no',
            'city_rel:id,city_name,state_id',
        ])->get();

        $grouped = [];
        foreach ($rows as $row) {
            $stateId = (int) $row->state_id;
            if (! isset($grouped[$stateId])) {
                $grouped[$stateId] = [
                    'state_id' => $stateId,
                    'state_name' => $row->state_rel->state_name ?? null,
                    'state_code' => $row->state_rel->state_code ?? null,
                    'state_order' => $row->state_rel->order_no ?? null,
                    'cities' => [],
                ];
            }

            if ($row->city_id) {
                $cityId = (int) $row->city_id;
                $grouped[$stateId]['cities'][$cityId] = [
                    'city_id' => $cityId,
                    'city_name' => $row->city_rel->city_name ?? null,
                ];
            }
        }

        $availability = array_values(array_map(function ($state) {
            $state['cities'] = array_values($state['cities']);
            usort($state['cities'], fn ($a, $b) => strcmp((string) $a['city_name'], (string) $b['city_name']));

            return $state;
        }, $grouped));

        usort($availability, function ($a, $b) {
            $orderA = $a['state_order'] ?? PHP_INT_MAX;
            $orderB = $b['state_order'] ?? PHP_INT_MAX;
            if ($orderA !== $orderB) {
                return $orderA <=> $orderB;
            }

            return strcmp((string) $a['state_name'], (string) $b['state_name']);
        });

        return array_map(function ($state) {
            unset($state['state_order']);

            return $state;
        }, $availability);
    }
}
