<?php

namespace Tests\Feature;

use App\Export\PaddyPriceExport;
use App\Http\Controllers\PaddyPriceController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Paddy price export must keep only the latest entry of each calendar day
 * per (state, mandi, quality, crop_year) — earlier same-day saves are duplicates.
 */
class PaddyPriceExportLastEntryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropIfExists('paddy_prices');
        Schema::dropIfExists('paddy_mandi');
        Schema::dropIfExists('paddy_states');
        Schema::dropIfExists('paddy_qualities');

        Schema::create('paddy_states', function (Blueprint $t) {
            $t->increments('id');
            $t->string('state')->nullable();
            $t->tinyInteger('status')->default(1);
        });
        Schema::create('paddy_mandi', function (Blueprint $t) {
            $t->increments('id');
            $t->string('mandi')->nullable();
            $t->unsignedInteger('state_id')->nullable();
            $t->tinyInteger('status')->default(1);
        });
        Schema::create('paddy_qualities', function (Blueprint $t) {
            $t->increments('id');
            $t->string('quality')->nullable();
            $t->string('type')->nullable();
            $t->tinyInteger('status')->default(1);
        });
        Schema::create('paddy_prices', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('mandi')->nullable();
            $t->unsignedInteger('state')->nullable();
            $t->unsignedInteger('quality_id')->nullable();
            $t->unsignedInteger('crop_year')->nullable();
            $t->string('min_hand_cutting_price')->nullable();
            $t->string('hand_cutting_price')->nullable();
            $t->string('min_machine_cutting_price')->nullable();
            $t->string('machine_cutting_price')->nullable();
            $t->string('moisture')->nullable();
            $t->string('total_arrivals')->nullable();
            $t->string('change')->nullable();
            $t->tinyInteger('status')->default(1);
            $t->timestamp('created_at')->nullable();
            $t->timestamp('updated_at')->nullable();
        });

        DB::table('paddy_states')->insert(['id' => 1, 'state' => 'DELHI', 'status' => 1]);
        DB::table('paddy_mandi')->insert(['id' => 10, 'mandi' => 'NARELA MANDI', 'state_id' => 1, 'status' => 1]);
        DB::table('paddy_qualities')->insert(['id' => 5, 'quality' => 'RH-10', 'type' => 'Non Basmati', 'status' => 1]);
    }

    private function callFiltered(string $from, string $to, bool $lastOfDayOnly = true)
    {
        $controller = new PaddyPriceController();
        $method = new ReflectionMethod(PaddyPriceController::class, 'filteredPaddyPrices');
        $method->setAccessible(true);

        return $method->invoke($controller, $from, $to, false, $lastOfDayOnly);
    }

    public function test_export_keeps_only_last_entry_per_day_per_group(): void
    {
        $insert = fn (string $time, string $hand, string $machine = '----') => DB::table('paddy_prices')->insert([
            'mandi' => 10,
            'state' => 1,
            'quality_id' => 5,
            'crop_year' => 2026,
            'min_hand_cutting_price' => '----',
            'hand_cutting_price' => $hand,
            'min_machine_cutting_price' => '----',
            'machine_cutting_price' => $machine,
            'moisture' => '----',
            'total_arrivals' => '0',
            'change' => 'Stable',
            'status' => 1,
            'created_at' => "2026-10-29 $time",
            'updated_at' => "2026-10-29 $time",
        ]);

        // Same state/mandi/quality/crop_year — three saves; keep only 11:00.
        $insert('09:00:00', '3400');
        $insert('10:00:00', '3450');
        $insert('11:00:00', '3500');

        // Different mandi same day — independent group.
        DB::table('paddy_mandi')->insert(['id' => 11, 'mandi' => 'OTHER', 'state_id' => 1, 'status' => 1]);
        DB::table('paddy_prices')->insert([
            'mandi' => 11, 'state' => 1, 'quality_id' => 5, 'crop_year' => 2026,
            'min_hand_cutting_price' => '----', 'hand_cutting_price' => '3600',
            'min_machine_cutting_price' => '----', 'machine_cutting_price' => '----',
            'moisture' => null, 'total_arrivals' => null, 'change' => 'Stable', 'status' => 1,
            'created_at' => '2026-10-29 10:30:00', 'updated_at' => '2026-10-29 10:30:00',
        ]);

        $rows = $this->callFiltered('2026-10-29', '2026-10-29', true);

        $this->assertCount(2, $rows);
        $narela = $rows->firstWhere('mandi', 10);
        $this->assertNotNull($narela);
        $this->assertSame('3500', $narela->hand_cutting_price);
        $this->assertFalse($rows->contains(fn ($r) => $r->hand_cutting_price === '3400'));
        $this->assertFalse($rows->contains(fn ($r) => $r->hand_cutting_price === '3450'));
    }

    public function test_list_without_dedupe_still_shows_all_rows(): void
    {
        DB::table('paddy_prices')->insert([
            [
                'mandi' => 10, 'state' => 1, 'quality_id' => 5, 'crop_year' => 2026,
                'min_hand_cutting_price' => '----', 'hand_cutting_price' => '3400',
                'min_machine_cutting_price' => '----', 'machine_cutting_price' => '----',
                'moisture' => null, 'total_arrivals' => null, 'change' => 'Stable', 'status' => 1,
                'created_at' => '2026-10-29 09:00:00', 'updated_at' => '2026-10-29 09:00:00',
            ],
            [
                'mandi' => 10, 'state' => 1, 'quality_id' => 5, 'crop_year' => 2026,
                'min_hand_cutting_price' => '----', 'hand_cutting_price' => '3500',
                'min_machine_cutting_price' => '----', 'machine_cutting_price' => '----',
                'moisture' => null, 'total_arrivals' => null, 'change' => 'Stable', 'status' => 1,
                'created_at' => '2026-10-29 11:00:00', 'updated_at' => '2026-10-29 11:00:00',
            ],
        ]);

        $rows = $this->callFiltered('2026-10-29', '2026-10-29', false);
        $this->assertCount(2, $rows);
    }

    public function test_export_map_hides_dash_and_zero_values(): void
    {
        DB::table('paddy_prices')->insert([
            'mandi' => 10, 'state' => 1, 'quality_id' => 5, 'crop_year' => 2026,
            'min_hand_cutting_price' => '----', 'hand_cutting_price' => '3500',
            'min_machine_cutting_price' => '----', 'machine_cutting_price' => '0',
            'moisture' => '----', 'total_arrivals' => '0', 'change' => 'Stable', 'status' => 1,
            'created_at' => '2026-10-29 11:00:00', 'updated_at' => '2026-10-29 11:00:00',
        ]);

        $row = $this->callFiltered('2026-10-29', '2026-10-29', true)->first();
        $mapped = (new PaddyPriceExport(collect([$row])))->collection()->first();

        $this->assertSame('', $mapped[5]); // min hand ----
        $this->assertSame('3500', (string) $mapped[6]);
        $this->assertSame('', $mapped[7]); // min machine ----
        $this->assertSame('', $mapped[8]); // max machine 0
        $this->assertSame('', $mapped[9]); // moisture ----
        $this->assertSame('', $mapped[10]); // arrivals 0
    }
}
