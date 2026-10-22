<?php

namespace Tests\Feature;

use App\Http\Controllers\WebBrandController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * public/api/web/vendor/list/{category} must not surface vendors whose
 * user account was deleted (users are hard-deleted, no soft deletes, while
 * web_business_details rows stay behind with is_active_listing=1).
 */
class WebVendorListDeletedUserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            ['category', function (Blueprint $t) {
                $t->increments('id');
                $t->string('category');
                $t->integer('status')->default(1);
                $t->integer('order')->nullable();
            }],
            ['users', function (Blueprint $t) {
                $t->increments('id');
                $t->string('name')->nullable();
                $t->string('email')->nullable();
                $t->string('user_from')->nullable();
                $t->integer('status')->default(1);
            }],
            ['web_business_details', function (Blueprint $t) {
                $t->increments('id');
                $t->integer('user_id')->nullable();
                $t->string('company_name')->nullable();
                $t->string('product')->nullable();
                $t->string('contactPerson')->nullable();
                $t->string('contactMobile')->nullable();
                $t->text('address')->nullable();
                $t->integer('is_sntc_recommended')->default(0);
                $t->integer('is_active_listing')->default(1);
                $t->string('selected_category')->nullable();
                $t->integer('status')->default(1);
            }],
            ['web_rice_bag_products', function (Blueprint $t) {
                $t->increments('id');
                $t->integer('user_id')->nullable();
                $t->integer('status')->default(0);
            }],
            ['web_rice_bag_product_packing_sizes', function (Blueprint $t) {
                $t->increments('id');
                $t->integer('product_id');
                $t->integer('sort_order')->default(0);
            }],
        ] as [$table, $blueprint]) {
            if (! Schema::hasTable($table)) {
                Schema::create($table, $blueprint);
            }
        }

        foreach (['category', 'users', 'web_business_details', 'web_rice_bag_product_packing_sizes'] as $table) {
            \DB::table($table)->delete();
        }
        \DB::table('web_rice_bag_products')->delete();

        // The vendor query uses MySQL's FIND_IN_SET for CSV selected_category —
        // register the same semantics for sqlite.
        \DB::connection()->getPdo()->sqliteCreateFunction('FIND_IN_SET', function ($needle, $haystack) {
            if ($needle === null || $haystack === null) {
                return 0;
            }

            return in_array((string) $needle, array_map('trim', explode(',', (string) $haystack)), true) ? 1 : 0;
        }, 2);

        \DB::table('category')->insert(['id' => 20, 'category' => 'Rice Bag', 'status' => 1]);
        \DB::table('users')->insert([
            ['id' => 7001, 'name' => 'Live Vendor', 'user_from' => 'web', 'status' => 1],
        ]);
        \DB::table('web_business_details')->insert([
            ['user_id' => 7001, 'company_name' => 'AAA Live Vendor', 'is_active_listing' => 1, 'status' => 1, 'selected_category' => '20'],
            // Deleted user: the users row no longer exists.
            ['user_id' => 9999, 'company_name' => 'ZZZ Deleted Vendor', 'is_active_listing' => 1, 'status' => 1, 'selected_category' => '20'],
            // Inactive listing: hidden for its own toggle reason, not the user.
            ['user_id' => 7001, 'company_name' => 'QQQ Inactive', 'is_active_listing' => 0, 'status' => 1, 'selected_category' => '20'],
        ]);
    }

    private function callList(): array
    {
        $controller = app(WebBrandController::class);

        return json_decode($controller->vendorList('20')->getContent(), true);
    }

    public function test_deleted_users_vendor_rows_hidden(): void
    {
        $data = $this->callList()['data'];

        $names = array_map(fn ($row) => $row['company_name'], $data);

        $this->assertContains('AAA Live Vendor', $names);
        $this->assertNotContains('ZZZ Deleted Vendor', $names, 'vendor row of a deleted user must not be listed');
        $this->assertNotContains('QQQ Inactive', $names);
    }
}
