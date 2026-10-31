<?php

namespace Tests\Feature;

use App\Support\UsdPricePackingFilter;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UsdPricePackingFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropIfExists('USD_defaultmaster');
        Schema::create('USD_defaultmaster', function ($t) {
            $t->increments('id');
            $t->string('bag_size')->nullable();
            $t->string('bag_type')->nullable();
            $t->integer('applied_for')->nullable();
            $t->integer('order')->nullable();
        });
    }

    public function test_resolve_master_ids_keeps_both_applied_for_when_type_is_selected(): void
    {
        \Illuminate\Support\Facades\DB::table('USD_defaultmaster')->insert([
            ['id' => 10, 'bag_size' => '50kg', 'bag_type' => 'PP', 'applied_for' => 0, 'order' => 1],
            ['id' => 20, 'bag_size' => '50kg', 'bag_type' => 'PP', 'applied_for' => 1, 'order' => 1],
            ['id' => 30, 'bag_size' => '40kg', 'bag_type' => 'Jute', 'applied_for' => 0, 'order' => 2],
            ['id' => 40, 'bag_size' => '40kg', 'bag_type' => 'Jute', 'applied_for' => 1, 'order' => 2],
        ]);

        $basmati = UsdPricePackingFilter::resolveMasterIds('basmati', '50kg_pp');
        $nonBasmati = UsdPricePackingFilter::resolveMasterIds('non-basmati', '50kg_pp');
        $all = UsdPricePackingFilter::resolveMasterIds(null, '50kg_pp');

        $this->assertEqualsCanonicalizing([10, 20], $basmati);
        $this->assertEqualsCanonicalizing([10, 20], $nonBasmati);
        $this->assertEqualsCanonicalizing([10, 20], $all);
    }

    public function test_resolve_master_ids_all_packings_includes_every_style_and_applied_for(): void
    {
        \Illuminate\Support\Facades\DB::table('USD_defaultmaster')->insert([
            ['id' => 10, 'bag_size' => '50kg', 'bag_type' => 'PP', 'applied_for' => 0, 'order' => 1],
            ['id' => 20, 'bag_size' => '50kg', 'bag_type' => 'PP', 'applied_for' => 1, 'order' => 1],
            ['id' => 30, 'bag_size' => '40kg', 'bag_type' => 'Jute', 'applied_for' => 0, 'order' => 2],
            ['id' => 40, 'bag_size' => '40kg', 'bag_type' => 'Jute', 'applied_for' => 1, 'order' => 2],
        ]);

        $ids = UsdPricePackingFilter::resolveMasterIds('basmati', null);

        $this->assertEqualsCanonicalizing([10, 20, 30, 40], $ids);
    }
}
