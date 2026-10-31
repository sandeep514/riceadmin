<?php

namespace Tests\Feature;

use App\Export\PaddyPricesApiExport;
use App\Support\PaddyPriceSeedFilter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaddyPricesApiExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('paddy_prices');
        Schema::dropIfExists('paddyMandi');
        Schema::dropIfExists('paddyStates');
        Schema::dropIfExists('paddy_qualities');

        Schema::create('paddyStates', function (Blueprint $t) {
            $t->increments('id');
            $t->string('state')->nullable();
            $t->tinyInteger('status')->default(1);
        });
        Schema::create('paddyMandi', function (Blueprint $t) {
            $t->increments('id');
            $t->string('mandi')->nullable();
            $t->unsignedInteger('state_id')->nullable();
            $t->tinyInteger('status')->default(1);
        });
        Schema::create('paddy_qualities', function (Blueprint $t) {
            $t->increments('id');
            $t->string('type')->nullable();
            $t->string('quality')->nullable();
            $t->integer('order')->nullable();
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

        DB::table('paddyStates')->insert(['id' => 1, 'state' => 'HARYANA', 'status' => 1]);
        DB::table('paddyMandi')->insert(['id' => 10, 'mandi' => 'KARNAL', 'state_id' => 1, 'status' => 1]);
        DB::table('paddy_qualities')->insert([
            ['id' => 1, 'type' => 'basmati', 'quality' => '1121', 'order' => 1, 'status' => 1],
            ['id' => 2, 'type' => 'basmati', 'quality' => '1509', 'order' => 2, 'status' => 1],
            ['id' => 3, 'type' => 'Non Basmati', 'quality' => 'PR-14', 'order' => 1, 'status' => 1],
            ['id' => 4, 'type' => 'non-basmati', 'quality' => '1121', 'order' => 2, 'status' => 1],
        ]);
    }

    public function test_filter_seeds_groups_by_canonical_type(): void
    {
        $basmati = PaddyPriceSeedFilter::listSeeds('basmati');
        $nonBasmati = PaddyPriceSeedFilter::listSeeds('non-basmati');
        $allBasmati = PaddyPriceSeedFilter::listSeeds(null);

        $this->assertEquals(['1121', '1509'], array_column($basmati, 'key'));
        $this->assertEquals(['PR-14', '1121'], array_column($nonBasmati, 'key'));
        $this->assertCount(4, $allBasmati);
    }

    public function test_resolve_quality_ids_scopes_seed_by_type(): void
    {
        $this->assertEqualsCanonicalizing(
            [1],
            PaddyPriceSeedFilter::resolveQualityIds('basmati', '1121')
        );
        $this->assertEqualsCanonicalizing(
            [4],
            PaddyPriceSeedFilter::resolveQualityIds('non-basmati', '1121')
        );
        $this->assertEqualsCanonicalizing(
            [1, 4],
            PaddyPriceSeedFilter::resolveQualityIds(null, '1121')
        );
        $this->assertEqualsCanonicalizing(
            [1, 2],
            PaddyPriceSeedFilter::resolveQualityIds('basmati', null)
        );
        $this->assertNull(PaddyPriceSeedFilter::resolveQualityIds(null, null));
    }

    public function test_export_keeps_last_entry_and_filters_by_type_and_seed(): void
    {
        $this->insertPrice(1, '2026-10-31 09:00:00', '3400');
        $this->insertPrice(1, '2026-10-31 11:00:00', '3500');
        $this->insertPrice(2, '2026-10-31 10:00:00', '3600');
        $this->insertPrice(3, '2026-10-31 10:30:00', '2200');
        $this->insertPrice(1, '2026-10-30 10:00:00', '3300');

        $basmati1121Ids = PaddyPriceSeedFilter::resolveQualityIds('basmati', '1121');
        $today = (new PaddyPricesApiExport('2026-10-31', '2026-10-31', $basmati1121Ids))->query()->get();

        $this->assertCount(1, $today);
        $this->assertSame('3500', $today->first()->hand_cutting_price);

        $allBasmati = PaddyPriceSeedFilter::resolveQualityIds('basmati', null);
        $historical = (new PaddyPricesApiExport('2026-10-30', '2026-10-31', $allBasmati))->query()->get();

        $this->assertCount(3, $historical);
        $this->assertFalse($historical->contains(fn ($row) => (int) $row->quality_id === 3));
        $this->assertFalse($historical->contains(fn ($row) => $row->hand_cutting_price === '3400'));
    }

    public function test_export_map_columns_and_hides_placeholder_values(): void
    {
        $this->insertPrice(1, '2026-10-31 11:00:00', '3500');

        $row = (new PaddyPricesApiExport('2026-10-31', '2026-10-31', [1]))->query()->first();
        $mapped = (new PaddyPricesApiExport('2026-10-31', '2026-10-31', [1]))->map($row);

        $this->assertSame('2026-10-31', $mapped[0]);
        $this->assertSame('HARYANA', $mapped[1]);
        $this->assertSame('KARNAL', $mapped[2]);
        $this->assertSame('1121', $mapped[3]);
        $this->assertSame(2026, (int) $mapped[4]);
        $this->assertSame('', $mapped[5]); // machine min ----
        $this->assertSame('', $mapped[6]); // machine max 0
        $this->assertSame('', $mapped[7]); // hand min ----
        $this->assertSame('3500', (string) $mapped[8]);
    }

    private function insertPrice(int $qualityId, string $at, string $hand): void
    {
        DB::table('paddy_prices')->insert([
            'mandi' => 10,
            'state' => 1,
            'quality_id' => $qualityId,
            'crop_year' => 2026,
            'min_hand_cutting_price' => '----',
            'hand_cutting_price' => $hand,
            'min_machine_cutting_price' => '----',
            'machine_cutting_price' => '0',
            'moisture' => '----',
            'total_arrivals' => '0',
            'change' => 'Stable',
            'status' => 1,
            'created_at' => $at,
            'updated_at' => $at,
        ]);
    }
}
