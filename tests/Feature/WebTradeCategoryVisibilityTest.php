<?php

namespace Tests\Feature;

use App\TradeQueriesINR;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Admin-created trades carrying web categories (trade_category_map) must be
 * visible ONLY to web users whose business profile (selected_category)
 * matches one of those categories. Trades without categories stay public.
 *
 * Regression scenario: "trade create → visible to that category only".
 */
class WebTradeCategoryVisibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('trade_query_milestone3')) {
            Schema::create('trade_query_milestone3', function (Blueprint $t) {
                $t->increments('id');
                $t->integer('status')->default(6);
                $t->integer('tradeType')->default(1);
                $t->string('validDays', 64)->nullable();
            });
        }
        if (! Schema::hasTable('trade_category_map')) {
            Schema::create('trade_category_map', function (Blueprint $t) {
                $t->increments('id');
                $t->integer('trade_id');
                $t->integer('category_id');
                $t->integer('status')->default(1);
            });
        }
        if (! Schema::hasTable('web_business_details')) {
            Schema::create('web_business_details', function (Blueprint $t) {
                $t->increments('id');
                $t->integer('user_id');
                $t->string('selected_category')->nullable();
            });
        }

        \DB::table('trade_query_milestone3')->delete();
        \DB::table('trade_category_map')->delete();
        \DB::table('web_business_details')->delete();

        // T1: no categories → public. T2: categories 5,7. T3: category 9.
        \DB::table('trade_query_milestone3')->insert([
            ['id' => 1, 'status' => 6, 'tradeType' => 1, 'validDays' => '2026-12-31 00:00:00'],
            ['id' => 2, 'status' => 6, 'tradeType' => 1, 'validDays' => '2026-12-31 00:00:00'],
            ['id' => 3, 'status' => 6, 'tradeType' => 1, 'validDays' => '2026-12-31 00:00:00'],
        ]);
        \DB::table('trade_category_map')->insert([
            ['trade_id' => 2, 'category_id' => 5, 'status' => 1],
            ['trade_id' => 2, 'category_id' => 7, 'status' => 1],
            ['trade_id' => 3, 'category_id' => 9, 'status' => 1],
        ]);
    }

    /**
     * @return array<int>
     */
    private function visibleTradeIds(int $userId): array
    {
        $method = new \ReflectionMethod(\App\Http\Controllers\ApiController::class, 'applyWebTradeCategoryVisibility');
        $method->setAccessible(true);

        $query = TradeQueriesINR::query();
        $method->invoke(app(\App\Http\Controllers\ApiController::class), $query, $userId);

        return $query->pluck('trade_query_milestone3.id')->map(fn ($id) => (int) $id)->sort()->values()->all();
    }

    public function test_trade_with_categories_hidden_from_other_category_user(): void
    {
        \DB::table('web_business_details')->insert(['user_id' => 101, 'selected_category' => '5']);

        $this->assertSame([1, 2], $this->visibleTradeIds(101), 'user of category 5 sees public trades + category-5 trade, NOT the category-9 trade');
    }

    public function test_trade_of_other_category_hidden_from_this_user(): void
    {
        \DB::table('web_business_details')->insert(['user_id' => 202, 'selected_category' => '9']);

        $this->assertSame([1, 3], $this->visibleTradeIds(202));
    }

    public function test_trade_without_categories_visible_to_everyone(): void
    {
        \DB::table('web_business_details')->insert(['user_id' => 303, 'selected_category' => '22']);

        $this->assertSame([1], $this->visibleTradeIds(303));
    }

    public function test_user_without_business_profile_sees_only_public_trades(): void
    {
        $this->assertSame([1], $this->visibleTradeIds(404));
    }
}
