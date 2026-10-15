<?php

namespace Tests\Feature;

use App\PaddyPrice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

/**
 * Regression: adding paddy prices must ALWAYS insert a new row.
 * A back-dated insert (created_at = price date, updated_at = save time)
 * must never UPDATE a previous row.
 *
 * Uses the default sqlite :memory: connection from phpunit.xml.
 */
class PaddyPriceInsertTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('paddy_prices');
        Schema::create('paddy_prices', function (Blueprint $table) {
            $table->id();
            $table->integer('mandi');
            $table->integer('state');
            $table->integer('quality_id');
            $table->integer('crop_year')->nullable();
            $table->string('hand_cutting_price', 256)->nullable();
            $table->string('machine_cutting_price', 256)->nullable();
            $table->string('moisture', 256)->nullable();
            $table->string('total_arrivals', 256)->nullable();
            $table->string('change', 256)->default('stable');
            $table->boolean('status')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    private function newPrice(array $attrs): PaddyPrice
    {
        $timestamps = [];
        foreach (['created_at', 'updated_at'] as $key) {
            if (array_key_exists($key, $attrs)) {
                $timestamps[$key] = $attrs[$key];
                unset($attrs[$key]);
            }
        }
        $row = new PaddyPrice($attrs);
        foreach ($timestamps as $key => $value) {
            $row->{$key} = $value;
        }
        $row->save();

        return $row;
    }

    public function test_adding_new_date_creates_new_row_and_leaves_previous_untouched(): void
    {
        $tz = config('app.timezone', 'Asia/Kolkata');

        // Existing row: price date 2026-10-12.
        $old = $this->newPrice([
            'mandi' => 1,
            'state' => 1,
            'quality_id' => 1,
            'crop_year' => 2026,
            'hand_cutting_price' => '100-200',
            'machine_cutting_price' => '100-200',
            'moisture' => '----',
            'total_arrivals' => '----',
            'change' => 'Stable',
            'status' => 1,
            'created_at' => Carbon::createFromFormat('Y-m-d', '2026-10-12', $tz)->startOfDay(),
            'updated_at' => Carbon::createFromFormat('Y-m-d', '2026-10-12', $tz)->startOfDay(),
        ]);
        $oldUpdatedAt = (string) $old->updated_at;

        // New submission for 2026-10-14 — mirrors PaddyPriceController@store():
        // always `new PaddyPrice + save()`, never an UPDATE of the old row.
        $entryDate = Carbon::createFromFormat('Y-m-d', '2026-10-14', $tz)->startOfDay();
        $new = $this->newPrice([
            'mandi' => 1,
            'state' => 1,
            'quality_id' => 1,
            'crop_year' => 2026,
            'hand_cutting_price' => '300-400',
            'machine_cutting_price' => '300-400',
            'moisture' => '----',
            'total_arrivals' => '----',
            'change' => 'Stable',
            'status' => 1,
            'created_at' => $entryDate,
            'updated_at' => Carbon::createFromFormat('Y-m-d H:i:s', '2026-10-14 10:00:00', $tz),
        ]);

        // New row, not a reuse of the old id.
        $this->assertNotEquals($old->id, $new->id);
        $this->assertSame(2, PaddyPrice::query()->count());

        // Old row untouched: same prices, same updated_at.
        $oldFresh = PaddyPrice::query()->find($old->id);
        $this->assertSame('100-200', $oldFresh->hand_cutting_price);
        $this->assertSame('2026-10-12', Carbon::parse($oldFresh->created_at)->format('Y-m-d'));
        $this->assertSame((string) Carbon::parse($oldUpdatedAt), (string) $oldFresh->updated_at);

        // New row carries the new date.
        $this->assertSame('2026-10-14', Carbon::parse($new->created_at)->format('Y-m-d'));
        $this->assertSame('300-400', $new->hand_cutting_price);
    }

    public function test_backdated_insert_explains_created_vs_updated_mismatch(): void
    {
        $tz = config('app.timezone', 'Asia/Kolkata');

        // Adding 2026-10-12's price late, on 2026-10-14 — same as store():
        // created_at = form date, updated_at = actual save time.
        $row = $this->newPrice([
            'mandi' => 1,
            'state' => 1,
            'quality_id' => 1,
            'crop_year' => 2026,
            'hand_cutting_price' => '100-200',
            'machine_cutting_price' => '100-200',
            'moisture' => '----',
            'total_arrivals' => '----',
            'change' => 'Stable',
            'status' => 1,
            'created_at' => Carbon::createFromFormat('Y-m-d', '2026-10-12', $tz)->startOfDay(),
            'updated_at' => Carbon::createFromFormat('Y-m-d H:i:s', '2026-10-14 10:00:00', $tz),
        ]);

        // created != updated here does NOT mean an old row was updated —
        // it is a single fresh INSERT (only 1 row in the table).
        $this->assertSame(1, PaddyPrice::query()->count());
        $this->assertSame('2026-10-12', Carbon::parse($row->created_at)->format('Y-m-d'));
        $this->assertSame('2026-10-14', Carbon::parse($row->updated_at)->format('Y-m-d'));
    }

    public function test_store_method_is_insert_only(): void
    {
        $src = file_get_contents(app_path('Http/Controllers/PaddyPriceController.php'));
        $this->assertNotFalse($src);

        // Extract store() body (up to the next public function).
        $start = strpos($src, 'public function store(');
        $this->assertNotFalse($start);
        $next = strpos($src, 'public function show(', $start);
        $body = substr($src, $start, $next ? $next - $start : 5000);

        $this->assertStringContainsString('new PaddyPrice', $body, 'store() must INSERT via new PaddyPrice');
        $this->assertStringNotContainsString('updateOrCreate', $body);
        $this->assertStringNotContainsString('firstOrCreate', $body);
        $this->assertStringNotContainsString('firstOrNew', $body);
        $this->assertStringNotContainsString('->update(', $body);
    }
}
