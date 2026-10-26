<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Admin reject (users/reject) must not 500, and the user's reject state must
 * record the reason (message + has_validation) with deactivated flag.
 */
class RejectWebUserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropIfExists('users');
        Schema::dropIfExists('web_user_subscription');
        Schema::dropIfExists('web_personal_details');
        Schema::dropIfExists('web_business_details');
        Schema::dropIfExists('web_user_attachment');
        Schema::dropIfExists('web_notifications');
        Schema::dropIfExists('notification');
        Schema::dropIfExists('user_interested_map_table');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('chatStatus');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('jobs');

        foreach ([
            'web_rice_bag_products', 'web_carton_products', 'web_cylinder_products',
            'web_lab_equipment_products', 'web_machinery_equipment_products',
            'web_clearing_agent_products', 'web_forwarder_products',
            'web_domestic_freight_products',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function createSchemas(): void
    {
        Schema::create('users', function ($t) {
            $t->increments('id');
            $t->string('name')->nullable();
            $t->string('email')->nullable();
            $t->string('password')->nullable();
            $t->integer('role')->nullable();
            $t->string('phone')->nullable();
            $t->string('mobile')->nullable();
            $t->string('user_from')->nullable();
            $t->integer('is_viewed_by_admin')->default(0);
            $t->integer('is_active_by_admin')->default(0);
            $t->unsignedTinyInteger('is_deactivated')->default(0);
            $t->text('has_validation')->nullable();
            $t->text('user_token')->nullable();
            $t->text('message')->nullable();
            $t->timestamp('created_at')->nullable();
            $t->timestamp('updated_at')->nullable();
            $t->rememberToken()->nullable();
        });
        Schema::create('web_user_subscription', function ($t) {
            $t->increments('id');
            $t->unsignedInteger('user_id')->nullable();
            $t->unsignedInteger('plan_id')->nullable();
            $t->string('subscription_type')->nullable();
            $t->string('period_start')->nullable();
            $t->string('period_end')->nullable();
            $t->string('status')->nullable();
            $t->string('amount')->nullable();
            $t->timestamps();
        });
        Schema::create('web_business_details', function ($t) {
            $t->increments('id');
            $t->unsignedInteger('user_id')->nullable();
            $t->string('company_name')->nullable();
            $t->string('selected_category')->nullable();
            $t->integer('is_sntc_recommended')->nullable();
            $t->integer('is_active_listing')->nullable();
            $t->timestamps();
        });
        Schema::create('web_user_attachments', function ($t) {
            $t->increments('id');
            $t->unsignedInteger('user_id')->nullable();
            $t->integer('status')->nullable();
        });
        Schema::create('web_notifications', function ($t) {
            $t->increments('id');
            $t->unsignedBigInteger('user_id')->nullable();
            $t->date('notify_date')->nullable();
            $t->string('title', 500)->nullable();
            $t->text('message')->nullable();
            $t->unsignedInteger('role_id')->nullable();
            $t->unsignedInteger('category_id')->nullable();
            $t->string('audience_mode', 32)->nullable();
            $t->string('broadcast_group_id')->nullable();
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
        });
        Schema::create('notification', function ($t) {
            $t->increments('id');
            $t->unsignedInteger('user_id')->nullable();
            $t->string('title')->nullable();
            $t->text('message')->nullable();
            $t->string('userAppType')->nullable();
            $t->integer('status')->nullable();
            $t->timestamps();
        });
        Schema::create('jobs', function ($t) {
            $t->increments('id');
            $t->text('queue')->nullable();
            $t->longText('payload')->nullable();
            $t->unsignedTinyInteger('attempts')->default(0);
            $t->unsignedInteger('reserved_at')->nullable();
            $t->unsignedInteger('available_at')->nullable();
            $t->unsignedInteger('created_at')->nullable();
        });
        Schema::create('user_interested_map_table', function ($t) {
            $t->increments('id');
            $t->unsignedInteger('user_id')->nullable();
            $t->integer('status')->default(1);
        });
        Schema::create('roles', function ($t) {
            $t->increments('id');
            $t->string('name')->nullable();
            $t->text('modules')->nullable();
        });
        Schema::create('chatStatus', function ($t) {
            $t->increments('id');
            $t->integer('status')->nullable();
        });
        Schema::create('modules', function ($t) {
            $t->increments('id');
            $t->string('title')->nullable();
            $t->string('url')->nullable();
            $t->integer('status')->nullable();
        });

        foreach ([
            'web_rice_bag_products' => 'packingSizes',
            'web_carton_products' => 'variants',
            'web_cylinder_products' => 'variants',
            'web_lab_equipment_products' => 'variants',
            'web_machinery_equipment_products' => 'variants',
            'web_clearing_agent_products' => 'particulars',
            'web_forwarder_products' => 'charges',
            'web_domestic_freight_products' => null,
        ] as $table => $child) {
            Schema::create($table, function ($t) use ($child) {
                $t->increments('id');
                $t->unsignedInteger('user_id')->nullable();
                $t->integer('status')->nullable();
                $t->timestamps();
                if ($child === 'packingSizes') {
                    $t->unsignedInteger('rice_bag_products_id')->nullable();
                } elseif ($child === 'variants') {
                    $t->unsignedInteger('product_id')->nullable();
                } elseif ($child === 'particulars') {
                    $t->unsignedInteger('clearing_agent_product_id')->nullable();
                } elseif ($child === 'charges') {
                    $t->unsignedInteger('forwarder_product_id')->nullable();
                }
            });
        }
    }

    public function test_reject_marks_user_and_does_not_crash(): void
    {
        $this->createSchemas();

        DB::table('roles')->insert(['id' => 2, 'name' => 'admin']);
        DB::table('chatStatus')->insert(['id' => 1, 'status' => 1]);

        DB::table('users')->insert([
            ['id' => 99, 'name' => 'Admin', 'email' => 'admin@test.local', 'password' => bcrypt('secret'), 'role' => 2],
            ['id' => 7, 'name' => 'WebUser', 'user_from' => 'web', 'email' => 'web@test.local', 'user_token' => 'tok123'],
        ]);

        $response = $this->actingAs(\App\User::find(99))->post(route('reject.user'), [
            'userId' => 7,
            'message' => 'Test rejection reason',
        ]);

        $response->assertRedirect();

        $user = DB::table('users')->where('id', 7)->first();
        $this->assertSame(1, (int) $user->is_deactivated);
        $this->assertSame(0, (int) $user->is_active_by_admin);
        $this->assertSame('Test rejection reason', trim((string) $user->has_validation));
        $this->assertSame('Test rejection reason', trim((string) $user->message));
        $this->assertNull($user->user_token);
    }
}
