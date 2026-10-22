<?php

namespace Tests\Feature;

use App\Http\Controllers\ApiController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * All-tab side order: sell trades before buy trades for
 * - Seller (4) / Supplier (6) role (existing behaviour)
 * - Manufacturer business category (selected_category names
 *   containing "manufactur", e.g. ids 2 and 9)  — new rule
 * Everyone else keeps buy-first.
 */
class WebTradeSideOrderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            ['roles', function (Blueprint $t) {
                $t->increments('id');
                $t->string('role_name')->nullable();
            }],
            ['users', function (Blueprint $t) {
                $t->increments('id');
                $t->integer('role')->nullable();
            }],
            ['category', function (Blueprint $t) {
                $t->increments('id');
                $t->string('category')->nullable();
                $t->integer('status')->default(1);
            }],
            ['web_business_details', function (Blueprint $t) {
                $t->increments('id');
                $t->integer('user_id')->nullable();
                $t->string('selected_category')->nullable();
                $t->integer('status')->default(1);
            }],
        ] as [$table, $blueprint]) {
            if (! Schema::hasTable($table)) {
                Schema::create($table, $blueprint);
            }
            \DB::table($table)->delete();
        }

        \DB::table('roles')->insert([
            ['id' => 4, 'role_name' => 'Seller'],
            ['id' => 6, 'role_name' => 'Supplier'],
            ['id' => 5, 'role_name' => 'Buyer'],
        ]);
        \DB::table('category')->insert([
            ['id' => 2, 'category' => 'Manufacturer', 'status' => 1],
            ['id' => 9, 'category' => 'Manufacturer & Exporter', 'status' => 1],
            ['id' => 20, 'category' => 'Rice Bag', 'status' => 1],
        ]);
    }

    private function sideOrderFor(array $userData, ?string $selectedCategory = null): string
    {
        \DB::table('users')->insert($userData);
        $userId = (int) \DB::table('users')->max('id');

        if ($selectedCategory !== null) {
            \DB::table('web_business_details')->insert([
                'user_id' => $userId,
                'selected_category' => $selectedCategory,
                'status' => 1,
            ]);
        }

        $method = new \ReflectionMethod(ApiController::class, 'resolveWebTradeListSideOrder');
        $method->setAccessible(true);

        return $method->invoke(app(ApiController::class), $userId);
    }

    public function test_seller_and_supplier_are_sell_first(): void
    {
        $this->assertSame('sell_first', $this->sideOrderFor(['id' => 5001, 'role' => 4]));
        $this->assertSame('sell_first', $this->sideOrderFor(['id' => 5002, 'role' => 6]));
    }

    public function test_manufacturer_category_is_sell_first(): void
    {
        $this->assertSame(
            'sell_first',
            $this->sideOrderFor(['id' => 5003, 'role' => 5], '2'),
            'Manufacturer (category 2) must get sell-first like Seller/Supplier'
        );
        $this->assertSame(
            'sell_first',
            $this->sideOrderFor(['id' => 5004, 'role' => 5], '9'),
            'Manufacturer & Exporter (category 9) must get sell-first too'
        );
    }

    public function test_buyer_and_non_manufacturer_categories_stay_buy_first(): void
    {
        $this->assertSame('buy_first', $this->sideOrderFor(['id' => 5005, 'role' => 5]));
        $this->assertSame('buy_first', $this->sideOrderFor(['id' => 5006, 'role' => 5], '20'));

        // No user at all → buy_first (guests).
        $method = new \ReflectionMethod(ApiController::class, 'resolveWebTradeListSideOrder');
        $method->setAccessible(true);
        $this->assertSame('buy_first', $method->invoke(app(ApiController::class), 0));
        $this->assertSame('buy_first', $method->invoke(app(ApiController::class), null));
    }
}
