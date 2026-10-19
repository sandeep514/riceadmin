<?php

namespace Tests\Feature;

use App\BuyQueriesINR;
use App\Buyerpackinginr;
use App\FutureSellQueriesINR;
use App\PublicPacking;
use App\SellQueriesINR;
use App\SellerPackingINR;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Convert-to-trade packing prefill must respect the fact that
 * buyer_packing_INR / sellerPackingINR / public_packing_milestone3 have
 * DIFFERENT id spaces. Regression: Miller "50 kg PP" buy queries were
 * prefilled with buyer id 2 ("50Kg PP+inner"), silently changing packing.
 */
class ConvertTradePackingPrefillTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('buyer_packing_INR')) {
            Schema::create('buyer_packing_INR', function ($t) {
                $t->increments('id');
                $t->string('packing');
                $t->string('description');
                $t->integer('status')->default(1);
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('sellerPackingINR')) {
            Schema::create('sellerPackingINR', function ($t) {
                $t->increments('id');
                $t->string('packing');
                $t->text('description');
                $t->integer('status')->default(1);
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('public_packing_milestone3')) {
            Schema::create('public_packing_milestone3', function ($t) {
                $t->increments('id');
                $t->string('size');
                $t->string('packing');
                $t->integer('order')->default(0);
                $t->integer('status')->default(1);
                $t->timestamps();
            });
        }

        Buyerpackinginr::query()->insert([
            ['id' => 1, 'packing' => '50Kg', 'description' => 'PP'],
            ['id' => 2, 'packing' => '50Kg', 'description' => 'PP+inner'],
            ['id' => 3, 'packing' => '40kg', 'description' => 'PP'],
        ]);
        SellerPackingINR::query()->insert([
            ['id' => 1, 'packing' => '55kg PP', 'description' => '55kg PP'],
            ['id' => 2, 'packing' => '50kg PP', 'description' => '50kg PP'],
            ['id' => 5, 'packing' => '30KG BOPP', 'description' => '30KG BOPP'],
        ]);
        PublicPacking::query()->insert([
            ['id' => 63, 'size' => '30kg', 'packing' => 'BOPP', 'order' => 1, 'status' => 1],
            ['id' => 45, 'size' => '20*1kg', 'packing' => 'Non-woven + PP Inner + HDPE outer', 'order' => 2, 'status' => 1],
        ]);
    }

    /**
     * @param  string  $modelClass
     * @param  array<string, mixed>  $attrs
     */
    private function prefill(string $modelClass, array $attrs, ?int $tradeType): ?array
    {
        $method = new \ReflectionMethod(\App\Http\Controllers\MasterController::class, 'buildConvertTradePrefill');
        $method->setAccessible(true);
        $model = new $modelClass;
        foreach ($attrs as $key => $value) {
            $model->setAttribute($key, $value);
        }

        return $method->invoke(app(\App\Http\Controllers\MasterController::class), $model, $tradeType);
    }

    public function test_buy_miller_50kg_prefills_buyer_50kg_pp_not_pp_inner(): void
    {
        $prefill = $this->prefill(BuyQueriesINR::class, ['packing_type' => 0, 'packing' => 0], 2);

        $this->assertSame(1, $prefill['ricepacking'], '50 kg PP must map to buyer id 1, not id 2 (50Kg PP+inner)');
        $this->assertSame(1, $prefill['packing_stream_type']);
    }

    public function test_buy_miller_55kg_has_no_buyer_equivalent_so_left_blank(): void
    {
        $prefill = $this->prefill(BuyQueriesINR::class, ['packing_type' => 0, 'packing' => 1], 2);

        $this->assertNull($prefill['ricepacking'], 'buy list has no 55kg — admin must pick, not get silently 50Kg PP');
    }

    public function test_miller_55kg_matches_seller_55kg_when_target_is_buy_side(): void
    {
        $prefill = $this->prefill(BuyQueriesINR::class, ['packing_type' => 0, 'packing' => 1], 1);

        $this->assertSame(1, $prefill['ricepacking']);
        $this->assertSame(1, $prefill['packing_stream_type']);
    }

    public function test_future_sell_enum_50kg_prefills_seller_50kg_pp(): void
    {
        $prefill = $this->prefill(FutureSellQueriesINR::class, ['packing' => 0], 1);

        $this->assertSame(2, $prefill['ricepacking'], 'future-sell enum 0 ("50 kg PP") maps to seller id 2');
    }

    public function test_future_sell_enum_55kg_prefills_seller_55kg_pp(): void
    {
        $prefill = $this->prefill(FutureSellQueriesINR::class, ['packing' => 1], 1);

        $this->assertSame(1, $prefill['ricepacking'], 'future-sell enum 1 ("55 kg PP") maps to seller id 1');
    }

    public function test_sell_bulk_id_passthrough_unchanged(): void
    {
        $prefill = $this->prefill(SellQueriesINR::class, ['packing' => 2], 1);

        $this->assertSame(2, $prefill['ricepacking']);
        $this->assertSame(1, $prefill['packing_stream_type']);
    }

    public function test_sell_branded_classic_id_only_maps_when_same_label_exists(): void
    {
        // Sell query with a public (branded) packing id 63 = "30kg BOPP".
        // Seller bulk list carries the same label (id 5) — mapping by label is
        // acceptable since the physical packing is identical.
        $prefill = $this->prefill(SellQueriesINR::class, ['packing' => 63], 1);

        $this->assertSame(5, $prefill['ricepacking']);
        $this->assertSame(1, $prefill['packing_stream_type']);
    }

    public function test_future_buy_private_id_not_carried_into_other_domain(): void
    {
        // Buyer-domain id 22 would mean something else in the seller list;
        // must resolve by label only and stay blank when unmatched.
        $prefill = $this->prefill(BuyQueriesINR::class, ['packing_type' => 1, 'packing' => 22], 4);

        $this->assertNull($prefill['ricepacking']);
        $this->assertNull($prefill['packing_stream_type']);
    }
}
