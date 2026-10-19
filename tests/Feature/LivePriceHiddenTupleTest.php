<?php

namespace Tests\Feature;

use App\LivePrice;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Admin-hidden live price tuples (min/max nulled OR zeroed) must never
 * resurface the previous day's price:
 *
 * 1. invalidLatestLivePriceTupleKeysForPairs must treat a missing `year`
 *    param the same as no cropYear filter (regression: the old strict
 *    where('cropYear', null) matched only cropYear IS NULL rows, so hidden
 *    tuples stayed visible in /api/prices/{state}/{type}).
 * 2. invalidLivePriceTupleKeysForRows (public state-list APIs) must hide
 *    states whose tuples' absolute-latest row is unusable.
 */
class LivePriceHiddenTupleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('live_prices')) {
            Schema::create('live_prices', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->string('form')->nullable();
                $table->string('state')->nullable();
                $table->integer('cropYear')->nullable();
                $table->string('min_price', 100)->nullable();
                $table->string('max_price', 100)->nullable();
                $table->integer('up_down')->default(1);
                $table->integer('cropGrade')->default(1);
                $table->text('opening')->nullable();
                $table->text('closing')->nullable();
                $table->string('monthStart', 256)->nullable();
                $table->string('monthEnd', 256)->nullable();
                $table->integer('state_order')->nullable();
                $table->integer('name_order')->nullable();
                $table->integer('form_order')->nullable();
                $table->integer('status')->default(1);
                $table->integer('is_updated_by_admin')->default(0);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        LivePrice::query()->delete();
    }

    private function row(array $attrs): LivePrice
    {
        return LivePrice::create(array_merge([
            'up_down' => 'up',
            'cropGrade' => 1,
            'status' => 1,
        ], $attrs));
    }

    private function invokePairs(string $state, $cropYear, array $pairs): array
    {
        $method = new \ReflectionMethod(\App\Http\Controllers\ApiController::class, 'invalidLatestLivePriceTupleKeysForPairs');
        $method->setAccessible(true);

        return $method->invoke(app(\App\Http\Controllers\ApiController::class), $state, $cropYear, $pairs);
    }

    private function invokeRows($rows): array
    {
        $method = new \ReflectionMethod(\App\Http\Controllers\ApiController::class, 'invalidLivePriceTupleKeysForRows');
        $method->setAccessible(true);

        return $method->invoke(app(\App\Http\Controllers\ApiController::class), $rows);
    }

    private function hiddenKey(array $rowOverrides): string
    {
        $row = new LivePrice(array_merge([
            'name' => 3,
            'form' => 5,
            'state' => 'UTTAR-PRADESH',
            'cropYear' => 2026,
            'min_price' => '100',
            'max_price' => '110',
        ], $rowOverrides));

        return implode('|', [
            (string) $row->name,
            (string) $row->form,
            (string) $row->state,
            (string) $row->cropYear,
        ]);
    }

    public function test_nulled_latest_row_hides_tuple_without_year_param(): void
    {
        $this->row([
            'name' => 3, 'form' => 5, 'state' => 'UTTAR-PRADESH', 'cropYear' => 2026,
            'min_price' => '100', 'max_price' => '110',
            'created_at' => '2026-10-17 10:00:00', 'updated_at' => '2026-10-17 10:00:00',
        ]);
        $this->row([
            'name' => 3, 'form' => 5, 'state' => 'UTTAR-PRADESH', 'cropYear' => 2026,
            'min_price' => null, 'max_price' => null,
            'created_at' => '2026-10-19 10:00:00', 'updated_at' => '2026-10-19 10:00:00',
        ]);

        // No year param — cropYear null. Previously matched only
        // cropYear IS NULL rows and returned [], so the stale price stayed.
        $this->assertArrayHasKey($this->hiddenKey([]), $this->invokePairs('UTTAR-PRADESH', null, [[3, 5]]));
    }

    public function test_zeroed_latest_row_hides_tuple(): void
    {
        $this->row([
            'name' => 3, 'form' => 5, 'state' => 'UTTAR-PRADESH', 'cropYear' => 2026,
            'min_price' => '100', 'max_price' => '110',
            'created_at' => '2026-10-17 10:00:00', 'updated_at' => '2026-10-17 10:00:00',
        ]);
        $this->row([
            'name' => 3, 'form' => 5, 'state' => 'UTTAR-PRADESH', 'cropYear' => 2026,
            'min_price' => 0, 'max_price' => 0,
            'created_at' => '2026-10-19 10:00:00', 'updated_at' => '2026-10-19 10:00:00',
        ]);

        $this->assertArrayHasKey($this->hiddenKey([]), $this->invokePairs('UTTAR-PRADESH', 2026, [[3, 5]]));
    }

    public function test_usable_latest_row_is_not_marked_invalid(): void
    {
        $this->row([
            'name' => 3, 'form' => 5, 'state' => 'UTTAR-PRADESH', 'cropYear' => 2026,
            'min_price' => '100', 'max_price' => '110',
            'created_at' => '2026-10-17 10:00:00', 'updated_at' => '2026-10-17 10:00:00',
        ]);

        $this->assertSame([], $this->invokePairs('UTTAR-PRADESH', 2026, [[3, 5]]));
    }

    public function test_rows_helper_hides_nulled_older_year_tuple(): void
    {
        $this->row([
            'name' => 3, 'form' => 5, 'state' => 'PUNJAB', 'cropYear' => 2026,
            'min_price' => '100', 'max_price' => '110',
            'created_at' => '2026-10-12 10:00:00', 'updated_at' => '2026-10-12 10:00:00',
        ]);
        $this->row([
            'name' => 3, 'form' => 5, 'state' => 'PUNJAB', 'cropYear' => 2026,
            'min_price' => '', 'max_price' => '',
            'created_at' => '2026-10-18 10:00:00', 'updated_at' => '2026-10-18 10:00:00',
        ]);
        $valid = $this->row([
            'name' => 8, 'form' => 5, 'state' => 'PUNJAB', 'cropYear' => 2026,
            'min_price' => '55', 'max_price' => '60',
            'created_at' => '2026-10-18 10:00:00', 'updated_at' => '2026-10-18 10:00:00',
        ]);

        $invalid = $this->invokeRows($valid->newQuery()->get());

        $this->assertArrayHasKey($this->hiddenKey([
            'name' => 3, 'state' => 'PUNJAB',
        ]), $invalid);
        $this->assertArrayNotHasKey($this->hiddenKey([
            'name' => 8, 'state' => 'PUNJAB',
        ]), $invalid);
    }

    public function test_rows_helper_with_empty_rows_returns_empty(): void
    {
        $this->assertSame([], $this->invokeRows(LivePrice::query()->whereRaw('1 = 0')->get()));
    }
}
