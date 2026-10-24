<?php

namespace Tests\Feature;

use App\LivePrice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Live prices report "last entry of each date":
 * For a name + form + state + crop year group, only the latest entry of each
 * calendar day must be returned (both on screen and CSV export).
 *
 * Regression scenario: "1121 steam min 1200 max 1300, then 1121 steam
 * min 1250 max 1350 on the same date — the export must keep 1250/1350 only".
 */
class LivePriceExportLastEntryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropIfExists('live_prices');
        Schema::dropIfExists('rice_names');
        Schema::dropIfExists('rice_forms');
        Schema::dropIfExists('rice_form_milestone3');
        Schema::create('live_prices', function ($t) {
            $t->increments('id');
            $t->integer('name')->nullable();
            $t->integer('form')->nullable();
            $t->integer('cropYear')->nullable();
            $t->string('state')->nullable();
            $t->string('min_price')->nullable();
            $t->string('max_price')->nullable();
            $t->string('opening')->nullable();
            $t->string('closing')->nullable();
            $t->timestamp('created_at')->nullable();
            $t->timestamp('updated_at')->nullable();
        });
        Schema::create('rice_names', function ($t) {
            $t->increments('id');
            $t->string('name');
        });
        Schema::create('rice_forms', function ($t) {
            $t->increments('id');
            $t->string('form_name')->nullable();
        });
        Schema::create('rice_form_milestone3', function ($t) {
            $t->increments('id');
            $t->string('name')->nullable();
        });
        Schema::create('users', function ($t) {
            $t->increments('id');
            $t->string('name')->nullable();
            $t->string('email')->nullable();
            $t->string('password')->nullable();
            $t->integer('role')->nullable();
            $t->rememberToken()->nullable();
        });
    }

    public function seedData(): void
    {
        DB::table('rice_names')->insert(['id' => 1121, 'name' => '1121']);
        DB::table('rice_forms')->insert(['id' => 5, 'form_name' => 'Steam']);

        $state = 'Punjab';
        $day = '2026-10-20';

        // Earlier entries of the same date — must NOT appear in the export.
        foreach ([
            ['1200', '1300', '09:00:00'],
            ['1225', '1325', '10:00:00'],
        ] as [$min, $max, $time]) {
            DB::table('live_prices')->insert([
                'name' => 1121, 'form' => 5, 'cropYear' => 2026, 'state' => $state,
                'min_price' => $min, 'max_price' => $max, 'opening' => '', 'closing' => '',
                'created_at' => "$day $time", 'updated_at' => "$day $time",
            ]);
        }

        // The expected "last entry" of the date.
        DB::table('live_prices')->insert([
            'name' => 1121, 'form' => 5, 'cropYear' => 2026, 'state' => $state,
            'min_price' => '1250', 'max_price' => '1350', 'opening' => '', 'closing' => '',
            'created_at' => "$day 11:00:00", 'updated_at' => "$day 11:00:00",
        ]);

        // Same date, different form — independent group, keeps its own last entry.
        DB::table('rice_forms')->insert(['id' => 6, 'form_name' => 'Raw']);
        DB::table('live_prices')->insert([
            'name' => 1121, 'form' => 6, 'cropYear' => 2026, 'state' => $state,
            'min_price' => '1100', 'max_price' => '1200', 'opening' => '', 'closing' => '',
            'created_at' => "$day 09:30:00", 'updated_at' => "$day 09:30:00",
        ]);
        DB::table('live_prices')->insert([
            'name' => 1121, 'form' => 6, 'cropYear' => 2026, 'state' => $state,
            'min_price' => '1130', 'max_price' => '1230', 'opening' => '', 'closing' => '',
            'created_at' => "$day 10:30:00", 'updated_at' => "$day 10:30:00",
        ]);

        // Next date — keeps its own last entry too.
        DB::table('live_prices')->insert([
            'name' => 1121, 'form' => 5, 'cropYear' => 2026, 'state' => $state,
            'min_price' => '1400', 'max_price' => '1500', 'opening' => '', 'closing' => '',
            'created_at' => '2026-10-21 09:00:00', 'updated_at' => '2026-10-21 09:00:00',
        ]);
    }

    public function test_export_contains_only_last_entry_per_date()
    {
        $this->seedData();

        DB::table('users')->insert(['id' => 1, 'name' => 'Admin', 'email' => 'admin@test.local', 'password' => bcrypt('secret'), 'role' => 2]);

        $response = $this->actingAs(\App\User::find(1))->get(route('reports.live-prices', [
            'from' => '2026-10-20',
            'to' => '2026-10-21',
            'export' => 'csv',
        ]));

        $response->assertOk();

        $content = $response->streamedContent();
        $lines = array_values(array_filter(explode("\n", $content), fn ($l) => trim($l) !== ''));

        // Header + 3 data rows: one last entry per (date, name, form, state, cropYear).
        $this->assertCount(4, $lines, "Expected header + 3 data rows, got:\n".$content);

        $dataCsv = implode("\n", array_slice($lines, 1));
        $date20 = '2026-10-20';
        $date21 = '2026-10-21';

        $this->assertCount(1, array_filter(explode("\n", $dataCsv), fn ($l) => str_contains($l, $date20) && str_contains($l, 'Steam') && str_contains($l, '1250')), $dataCsv);

        // Last entries kept.
        $this->assertStringContainsString('1121,Steam,Punjab,'.$date20.',2026,1250,1350', $dataCsv);
        $this->assertStringContainsString('1121,Raw,Punjab,'.$date20.',2026,1130,1230', $dataCsv);
        $this->assertStringNotContainsString(',2026,1200,1300,', $dataCsv);
        $this->assertStringNotContainsString(',2026,1225,1325', $dataCsv);
        $this->assertStringNotContainsString(',2026,1100,1200,', $dataCsv);

        // Next date's own last entry kept.
        $this->assertStringContainsString('1121,Steam,Punjab,'.$date21.',2026,1400,1500', $dataCsv);
    }
}
