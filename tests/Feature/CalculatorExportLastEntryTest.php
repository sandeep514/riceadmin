<?php

namespace Tests\Feature;

use App\Export\CalculatorExport;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Calculator export "last entry of each date":
 * For a (quality, packing) group only the latest entry of each calendar day
 * must be exported — earlier same-day saves are superseded duplicates.
 *
 * Regression scenario: "1121 old rice with 5kg PP packing saved three times on
 * 2026-10-20 — the export must keep the 11:00 values only".
 */
class CalculatorExportLastEntryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropIfExists('USD_prices');
        Schema::dropIfExists('quality_master');
        Schema::dropIfExists('USD_defaultmaster');
        Schema::create('quality_master', function ($t) {
            $t->increments('id');
            $t->string('quality')->nullable();
            $t->string('quality_name')->nullable();
            $t->string('quality_type')->nullable();
        });
        Schema::create('USD_defaultmaster', function ($t) {
            $t->increments('id');
            $t->string('bag_size')->nullable();
            $t->string('bag_type')->nullable();
        });
        Schema::create('USD_prices', function ($t) {
            $t->increments('id');
            $t->unsignedInteger('rice')->nullable();
            $t->string('ricemin')->nullable();
            $t->string('ricemax')->nullable();
            $t->string('transportmin')->nullable();
            $t->string('transportmax')->nullable();
            $t->string('dollarrate')->nullable();
            $t->string('fobmin')->nullable();
            $t->string('fobmax')->nullable();
            $t->unsignedInteger('usd_defaultMaster_id')->nullable();
            $t->timestamp('created_at')->nullable();
            $t->timestamp('updated_at')->nullable();
        });
    }

    public function seedData(): void
    {
        // Quality 1 = basmati "Old", Quality 2 = non-basmati "New".
        \Illuminate\Support\Facades\DB::table('quality_master')->insert([
            ['id' => 1, 'quality' => '1121', 'quality_name' => 'Old', 'quality_type' => 'basmati'],
            ['id' => 2, 'quality' => '1121', 'quality_name' => 'New', 'quality_type' => 'non-basmati'],
        ]);
        // Packing 25 = 5kg PP, Packing 26 = 25kg Jute.
        \Illuminate\Support\Facades\DB::table('USD_defaultmaster')->insert([
            ['id' => 25, 'bag_size' => '5kg', 'bag_type' => 'PP'],
            ['id' => 26, 'bag_size' => '25kg', 'bag_type' => 'Jute'],
        ]);

        $insert = fn (int $rice, int $packing, string $day, string $time, string $min, string $max, string $fobmin = '10') => \Illuminate\Support\Facades\DB::table('USD_prices')->insert([
            'rice' => $rice, 'ricemin' => $min, 'ricemax' => $max,
            'transportmin' => '0', 'transportmax' => '0', 'dollarrate' => '85',
            'fobmin' => $fobmin, 'fobmax' => '12',
            'usd_defaultMaster_id' => $packing,
            'created_at' => "$day $time", 'updated_at' => "$day $time",
        ]);

        // Quality 1 + Packing 25, three saves on 2026-10-20 — keep only 11:00.
        $insert(1, 25, '2026-10-20', '09:00:00', '100', '110');
        $insert(1, 25, '2026-10-20', '10:00:00', '105', '115');
        $insert(1, 25, '2026-10-20', '11:00:00', '110', '120');

        // Same day, same quality, different packing — independent group.
        $insert(1, 26, '2026-10-20', '09:30:00', '90', '100');

        // Same day, different quality, same packing — independent group.
        $insert(2, 25, '2026-10-20', '10:30:00', '115', '125');

        // Next day, same quality + packing — keeps its own last entry.
        $insert(1, 25, '2026-10-21', '09:00:00', '120', '130');
    }

    public function test_export_keeps_only_last_entry_per_day_per_quality_and_packing(): void
    {
        $this->seedData();

        $query = (new CalculatorExport('2026-10-20', '2026-10-21'))->query()->get();

        // One last entry per (date × quality × packing): 4 rows expected.
        $this->assertCount(4, $query);

        $keys = $query->map(fn ($row) => [
            substr((string) $row->created_at, 0, 10), $row->rice, $row->usd_defaultMaster_id, $row->ricemin, $row->ricemax,
        ])->toArray();

        // Last entries per day kept.
        $this->assertContains(['2026-10-20', 1, 25, '110', '120'], $keys);
        $this->assertContains(['2026-10-20', 1, 26, '90', '100'], $keys);
        $this->assertContains(['2026-10-20', 2, 25, '115', '125'], $keys);
        $this->assertContains(['2026-10-21', 1, 25, '120', '130'], $keys);

        // Superseded same-day saves dropped.
        $this->assertNotContains(['2026-10-20', 1, 25, '100', '110'], $keys);
        $this->assertNotContains(['2026-10-20', 1, 25, '105', '115'], $keys);
    }

    public function test_unfiltered_export_reaches_years_of_rows(): void
    {
        $this->seedData();

        $query = (new CalculatorExport())->query()->get();

        // Empty from/to/packing filters must keep every group's last entry of each day.
        $this->assertCount(4, $query);
    }

    public function test_packing_filter_applies_before_dedupe(): void
    {
        $this->seedData();

        $query = (new CalculatorExport('2026-10-20', '2026-10-21', 25))->query()->get();

        $this->assertCount(3, $query);
        $this->assertTrue($query->contains(fn ($row) => (int) $row->usd_defaultMaster_id === 25 && $row->ricemin === '110'));
        $this->assertTrue($query->contains(fn ($row) => (int) $row->usd_defaultMaster_id === 25 && $row->ricemin === '115'));
        $this->assertTrue($query->contains(fn ($row) => (int) $row->usd_defaultMaster_id === 25 && $row->ricemin === '120'));
        $this->assertFalse($query->contains(fn ($row) => (int) $row->usd_defaultMaster_id === 26));
    }

    public function test_zero_fob_rows_are_excluded(): void
    {
        $this->seedData();

        \Illuminate\Support\Facades\DB::table('USD_prices')->insert([
            'rice' => 1,
            'ricemin' => '999',
            'ricemax' => '1000',
            'transportmin' => '0',
            'transportmax' => '0',
            'dollarrate' => '85',
            'fobmin' => '0',
            'fobmax' => '0',
            'usd_defaultMaster_id' => 25,
            'created_at' => '2026-10-22 12:00:00',
            'updated_at' => '2026-10-22 12:00:00',
        ]);

        $query = (new CalculatorExport('2026-10-22', '2026-10-22'))->query()->get();

        $this->assertCount(0, $query);
        $this->assertFalse($query->contains(fn ($row) => $row->ricemin === '999'));
    }

    public function test_null_and_blank_fob_rows_are_excluded(): void
    {
        $this->seedData();

        \Illuminate\Support\Facades\DB::table('USD_prices')->insert([
            [
                'rice' => 1, 'ricemin' => '1', 'ricemax' => '2',
                'transportmin' => '0', 'transportmax' => '0', 'dollarrate' => '85',
                'fobmin' => null, 'fobmax' => '5', 'usd_defaultMaster_id' => 25,
                'created_at' => '2026-10-23 10:00:00', 'updated_at' => '2026-10-23 10:00:00',
            ],
            [
                'rice' => 1, 'ricemin' => '3', 'ricemax' => '4',
                'transportmin' => '0', 'transportmax' => '0', 'dollarrate' => '85',
                'fobmin' => '  ', 'fobmax' => '5', 'usd_defaultMaster_id' => 25,
                'created_at' => '2026-10-23 11:00:00', 'updated_at' => '2026-10-23 11:00:00',
            ],
        ]);

        $query = (new CalculatorExport('2026-10-23', '2026-10-23'))->query()->get();

        $this->assertCount(0, $query);
    }

    public function test_map_hides_zero_and_null_numeric_cells(): void
    {
        $this->seedData();

        $row = (new CalculatorExport('2026-10-20', '2026-10-20', 26))->query()->first();
        $this->assertNotNull($row);

        $mapped = (new CalculatorExport('2026-10-20', '2026-10-20', 26))->map($row);

        $this->assertSame('2026-10-20', $mapped[0]);
        $this->assertSame('25kg', $mapped[3]);
        $this->assertSame('Jute', $mapped[4]);
        $this->assertSame('10', (string) $mapped[5]);
        $this->assertSame('12', (string) $mapped[6]);
    }

    public function test_type_filter_keeps_matching_quality_type(): void
    {
        $this->seedData();

        $basmati = (new CalculatorExport('2026-10-20', '2026-10-21', null, 'basmati'))->query()->get();
        $this->assertCount(3, $basmati);
        $this->assertTrue($basmati->every(fn ($row) => (int) $row->rice === 1));

        $nonBasmati = (new CalculatorExport('2026-10-20', '2026-10-21', null, 'non-basmati'))->query()->get();
        $this->assertCount(1, $nonBasmati);
        $this->assertTrue($nonBasmati->every(fn ($row) => (int) $row->rice === 2));
    }

    public function test_packing_and_type_filters_combine_with_fob_rule(): void
    {
        $this->seedData();

        \Illuminate\Support\Facades\DB::table('USD_prices')->insert([
            'rice' => 1,
            'ricemin' => '200',
            'ricemax' => '210',
            'transportmin' => '0',
            'transportmax' => '0',
            'dollarrate' => '85',
            'fobmin' => '0',
            'fobmax' => '5',
            'usd_defaultMaster_id' => 25,
            'created_at' => '2026-10-20 15:00:00',
            'updated_at' => '2026-10-20 15:00:00',
        ]);

        // Zero FOB latest same-day row must not replace the previous non-zero last entry.
        $query = (new CalculatorExport('2026-10-20', '2026-10-20', 25, 'basmati'))->query()->get();

        $this->assertCount(1, $query);
        $this->assertSame('110', $query->first()->ricemin);
        $this->assertFalse($query->contains(fn ($row) => $row->ricemin === '200'));
    }
}
