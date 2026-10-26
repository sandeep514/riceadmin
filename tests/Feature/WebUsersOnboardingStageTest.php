<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Web users list (administrator/web/users): the "Onboarding Stage" column must
 * show how far each user got in the journey:
 * OTP login only → profile completed → documents uploaded → trial started → paid plan.
 */
class WebUsersOnboardingStageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropIfExists('users');
        Schema::dropIfExists('web_user_subscription');
        Schema::dropIfExists('web_personal_details');
        Schema::dropIfExists('web_business_details');
        Schema::dropIfExists('web_user_attachment');
        Schema::dropIfExists('category');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('chatStatus');
        Schema::dropIfExists('modules');

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
        Schema::create('web_personal_details', function ($t) {
            $t->increments('id');
            $t->unsignedInteger('user_id')->nullable();
            $t->timestamps();
        });
        Schema::create('web_business_details', function ($t) {
            $t->increments('id');
            $t->unsignedInteger('user_id')->nullable();
            $t->string('company_name')->nullable();
            $t->string('selected_category')->nullable();
            $t->integer('is_sntc_recommended')->nullable();
            $t->integer('is_active_listing')->nullable();
        });
        Schema::create('web_user_attachment', function ($t) {
            $t->increments('id');
            $t->unsignedInteger('user_id')->nullable();
            $t->string('panCard')->nullable();
            $t->string('farmer_file')->nullable();
            $t->string('gst_fssai')->nullable();
            $t->string('gstCard')->nullable();
            $t->string('fssaiCard')->nullable();
            $t->integer('status')->nullable();
        });
        Schema::create('category', function ($t) {
            $t->increments('id');
            $t->string('category')->nullable();
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

        DB::table('roles')->insert(['id' => 2, 'name' => 'admin']);
        DB::table('chatStatus')->insert(['id' => 1, 'status' => 1]);
        DB::table('category')->insert(['id' => 20, 'category' => 'Trader']);
    }

    private function seedStages(): void
    {
        $dates = [
            '2026-10-01 09:00:00',
            '2026-10-05 10:00:00',
            '2026-10-10 11:00:00',
            '2026-10-15 12:00:00',
            '2026-10-20 13:00:00',
            '2026-10-25 14:00:00',
        ];
        $names = ['OtpOnly', 'Profile', 'Docs', 'Trial', 'PaidPendingActivation', 'PaidActive'];
        $rows = [];
        foreach ($names as $i => $name) {
            $rows[] = ['id' => $i + 1, 'name' => $name, 'user_from' => 'web', 'created_at' => $dates[$i], 'updated_at' => $dates[$i]];
        }
        DB::table('users')->insert($rows);
        DB::table('web_personal_details')->insert(['user_id' => 2]);
        DB::table('web_user_attachment')->insert(['user_id' => 3, 'panCard' => 'uploads/pan.jpg']);
        DB::table('web_user_subscription')->insert([
            ['user_id' => 4, 'subscription_type' => 'trial', 'status' => 1],
            ['user_id' => 5, 'subscription_type' => 'monthly', 'status' => 1],
            ['user_id' => 6, 'subscription_type' => 'yearly', 'status' => 1],
        ]);
        DB::table('users')->where('id', 6)->update(['is_active_by_admin' => 1]);
    }

    public function test_onboarding_stage_column_reflects_user_journey(): void
    {
        $this->seedStages();
        DB::table('users')->insert([
            ['id' => 99, 'name' => 'Admin', 'email' => 'admin@test.local', 'password' => bcrypt('secret'), 'role' => 2],
        ]);

        $response = $this->actingAs(\App\User::find(99))->get(route('web.user'));
        $response->assertOk();

        $html = $response->getContent();

        $this->assertStringContainsString('Onboarding Stage', $html);
        $this->assertStringContainsString('OTP login only', $html);
        $this->assertStringContainsString('Profile completed', $html);
        $this->assertStringContainsString('Documents uploaded', $html);
        $this->assertStringContainsString('Trial started', $html);
        $this->assertStringContainsString('Paid plan — Monthly', $html);
        $this->assertStringContainsString('Paid plan — Active', $html);
    }

    public function test_from_to_filter_scopes_list_and_export(): void
    {
        $this->seedStages();
        DB::table('users')->insert([
            ['id' => 99, 'name' => 'Admin', 'email' => 'admin@test.local', 'password' => bcrypt('secret'), 'role' => 2],
        ]);

        $auth = ['actingAs' => \App\User::find(99)];

        // Screen: only the 10-05..10-15 window.
        $screen = $this->actingAs($auth['actingAs'])->get(route('web.user', ['from' => '2026-10-05', 'to' => '2026-10-15']));
        $screen->assertOk();
        $html = $screen->getContent();
        $this->assertStringContainsString('Profile', $html);
        $this->assertStringContainsString('Trial', $html);
        $this->assertStringNotContainsString('PaidPendingActivation', $html);
        $this->assertStringNotContainsString('OtpOnly', $html);

        // Export: same window, CSV without pagination.
        $export = $this->actingAs($auth['actingAs'])->get(route('web.user', ['from' => '2026-10-05', 'to' => '2026-10-15', 'export' => 'csv']));
        $export->assertOk();

        $csv = $export->streamedContent();
        $this->assertStringContainsString('"User ID",Name,Email,Phone,"Joined At",Category,"Onboarding Stage"', $csv);
        $this->assertStringContainsString('Profile completed', $csv);
        $this->assertStringContainsString('Trial started', $csv);
        $this->assertStringNotContainsString('PaidPendingActivation', $csv);
        $this->assertStringNotContainsString('OtpOnly', $csv);
    }
}
