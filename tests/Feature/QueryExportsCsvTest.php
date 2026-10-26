<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Server-side CSV exports of the INR query lists must contain every
 * database row (not just the rendered page or whatever the broken
 * client-side DataTables export produced).
 */
class QueryExportsCsvTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('buy_query_milestone3');
        Schema::dropIfExists('sell_query_milestone3');
        Schema::dropIfExists('rice_names');
        Schema::dropIfExists('rice_form_milestone3');
        Schema::dropIfExists('users');
        Schema::dropIfExists('chatStatus');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('sellerPackingINR');
        Schema::dropIfExists('buyer_packing_INR');
        Schema::dropIfExists('wand');
        Schema::dropIfExists('wandType');

        Schema::create('sellerPackingINR', function ($t) {
            $t->increments('id');
            $t->string('packing')->nullable();
            $t->string('description')->nullable();
            $t->integer('status')->nullable();
        });
        Schema::create('buyer_packing_INR', function ($t) {
            $t->increments('id');
            $t->string('packing')->nullable();
            $t->string('description')->nullable();
            $t->integer('status')->nullable();
        });
        Schema::create('wand', function ($t) {
            $t->increments('id');
            $t->unsignedInteger('wandTypeId')->nullable();
            $t->string('value')->nullable();
        });
        Schema::create('wandType', function ($t) {
            $t->increments('id');
            $t->string('type')->nullable();
        });

        Schema::create('users', function ($t) {
            $t->increments('id');
            $t->string('name')->nullable();
            $t->string('email')->nullable();
            $t->string('password')->nullable();
            $t->integer('role')->nullable();
            $t->string('mobile')->nullable();
            $t->rememberToken()->nullable();
        });
        Schema::create('chatStatus', function ($t) {
            $t->increments('id');
            $t->integer('status')->nullable();
        });
        Schema::create('roles', function ($t) {
            $t->increments('id');
            $t->string('name')->nullable();
            $t->text('modules')->nullable();
        });
        Schema::create('modules', function ($t) {
            $t->increments('id');
            $t->string('title')->nullable();
            $t->string('url')->nullable();
            $t->integer('status')->nullable();
        });
        Schema::create('rice_names', function ($t) {
            $t->increments('id');
            $t->string('name')->nullable();
            $t->string('type')->nullable();
        });
        Schema::create('rice_form_milestone3', function ($t) {
            $t->increments('id');
            $t->string('name')->nullable();
        });
        Schema::create('buy_query_milestone3', function ($t) {
            $t->increments('id');
            $t->integer('quality_type')->nullable();
            $t->unsignedInteger('quality')->nullable();
            $t->unsignedInteger('quality_form')->nullable();
            $t->unsignedInteger('grade')->nullable();
            $t->integer('packing_type')->nullable();
            $t->unsignedInteger('packing')->nullable();
            $t->string('quantity')->nullable();
            $t->text('additional_info')->nullable();
            $t->unsignedInteger('created_by')->nullable();
            $t->text('remarks')->nullable();
            $t->string('type')->nullable();
            $t->string('contactPerson')->nullable();
            $t->string('contactMobile')->nullable();
            $t->integer('status')->nullable();
            $t->timestamps();
        });
        Schema::create('sell_query_milestone3', function ($t) {
            $t->increments('id');
            $t->integer('quality_type')->nullable();
            $t->unsignedInteger('quality')->nullable();
            $t->unsignedInteger('qualityForm')->nullable();
            $t->unsignedInteger('grade')->nullable();
            $t->unsignedInteger('packing')->nullable();
            $t->string('quantity')->nullable();
            $t->string('offerPrice')->nullable();
            $t->string('validDays')->nullable();
            $t->string('warehouselocation')->nullable();
            $t->unsignedInteger('created_by')->nullable();
            $t->text('remarks')->nullable();
            $t->string('type')->nullable();
            $t->string('contactperson')->nullable();
            $t->string('contactMobile')->nullable();
            $t->integer('status')->nullable();
            $t->timestamps();
        });

        DB::table('chatStatus')->insert(['id' => 1, 'status' => 1]);
        DB::table('roles')->insert(['id' => 2, 'name' => 'admin']);
        DB::table('users')->insert(['id' => 1, 'name' => 'Admin', 'email' => 'admin@test.local', 'password' => bcrypt('secret'), 'role' => 2]);
        DB::table('users')->insert(['id' => 7, 'name' => 'Buyer Raj', 'email' => 'buyer@test.local', 'role' => 7, 'mobile' => '9999']);
        DB::table('rice_names')->insert(['id' => 11, 'name' => '1121', 'type' => 'Basmati']);
        DB::table('rice_form_milestone3')->insert(['id' => 5, 'name' => 'Steam']);
        DB::table('wandType')->insert(['id' => 2, 'type' => 'Black']);
        DB::table('wand')->insert(['id' => 3, 'wandTypeId' => 2, 'value' => '12-14']);
        DB::table('sellerPackingINR')->insert(['id' => 12, 'packing' => '25 kg', 'description' => 'PP', 'status' => 1]);
        DB::table('buyer_packing_INR')->insert(['id' => 10, 'packing' => '20 kg', 'description' => 'Jute', 'status' => 1]);

        DB::table('buy_query_milestone3')->insert([
            'id' => 101, 'quality_type' => 1, 'quality' => 11, 'quality_form' => 5,
            'grade' => 3, 'packing_type' => 0, 'quantity' => '2 containers',
            'additional_info' => 'Gulf port', 'created_by' => 7,
            'contactPerson' => 'Aman', 'contactMobile' => '8888',
            'remarks' => 'good lead', 'status' => 1,
            'created_at' => '2026-10-20 10:00:00', 'updated_at' => '2026-10-20 10:00:00',
        ]);
        DB::table('sell_query_milestone3')->insert([
            'id' => 201, 'quality' => 11, 'qualityForm' => 5, 'grade' => 3,
            'packing' => 12, 'quantity' => '500 MT',
            'offerPrice' => '1220', 'validDays' => '10',
            'warehouselocation' => 'Karnal', 'created_by' => 7,
            'contactperson' => 'Simran', 'contactMobile' => '7777',
            'remarks' => 'sample ready', 'status' => 1,
            'created_at' => '2026-10-21 09:00:00', 'updated_at' => '2026-10-21 09:00:00',
        ]);
    }

    public function test_buy_queries_export_contains_every_database_row(): void
    {
        $response = $this->actingAs(\App\User::find(1))->get(route('master.export.buy.queries.INR'));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('ID,"Quality Type",Quality,Grade,"Packing Type",Packing,Quantity', $content);
        $this->assertStringContainsString('101,Basmati', $content);
        $this->assertStringContainsString('1121 Steam', $content);
        $this->assertStringContainsString('Black 12-14', $content);
        $this->assertStringContainsString('2 containers', $content);
        $this->assertStringContainsString('Gulf port', $content);
        $this->assertStringContainsString('Buyer Raj - 9999', $content);
        $this->assertStringContainsString('Aman,8888', $content);
        $this->assertStringContainsString('good lead', $content);
        $this->assertStringContainsString('Pending', $content);
    }

    public function test_future_buy_queries_export_works(): void
    {
        Schema::dropIfExists('future_buy_query_milestone3');
        Schema::create('future_buy_query_milestone3', function ($t) {
            $t->increments('id');
            $t->integer('quality_type')->nullable();
            $t->unsignedInteger('quality')->nullable();
            $t->unsignedInteger('quality_form')->nullable();
            $t->unsignedInteger('grade')->nullable();
            $t->integer('packing_type')->nullable();
            $t->unsignedInteger('packing')->nullable();
            $t->string('quantity')->nullable();
            $t->text('additional_info')->nullable();
            $t->unsignedInteger('created_by')->nullable();
            $t->text('remarks')->nullable();
            $t->string('type')->nullable();
            $t->string('contactPerson')->nullable();
            $t->string('contactMobile')->nullable();
            $t->integer('status')->nullable();
            $t->timestamps();
        });
        DB::table('future_buy_query_milestone3')->insert([
            'id' => 301, 'quality_type' => 1, 'quality' => 11, 'quality_form' => 5,
            'created_by' => 7, 'status' => 0,
            'created_at' => '2026-10-22 09:00:00', 'updated_at' => '2026-10-22 09:00:00',
        ]);

        $response = $this->actingAs(\App\User::find(1))->get(route('master.export.future.buy.queries.INR'));

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('301,Basmati', $content);
        $this->assertStringContainsString('1121 Steam', $content);
        $this->assertStringContainsString('closed', $content);
    }

    public function test_sell_and_future_sell_queries_exports_work(): void
    {
        Schema::dropIfExists('future_sell_query_milestone3');
        Schema::create('future_sell_query_milestone3', function ($t) {
            $t->increments('id');
            $t->integer('quality_type')->nullable();
            $t->unsignedInteger('quality')->nullable();
            $t->unsignedInteger('quality_form')->nullable();
            $t->unsignedInteger('grade')->nullable();
            $t->unsignedInteger('packing')->nullable();
            $t->string('quantity')->nullable();
            $t->string('offerPrice')->nullable();
            $t->string('validDays')->nullable();
            $t->string('warehouselocation')->nullable();
            $t->unsignedInteger('created_by')->nullable();
            $t->text('remarks')->nullable();
            $t->string('type')->nullable();
            $t->string('contactperson')->nullable();
            $t->string('contactMobile')->nullable();
            $t->integer('status')->nullable();
            $t->timestamps();
        });
        DB::table('future_sell_query_milestone3')->insert([
            'id' => 401, 'quality' => 11, 'quality_form' => 5,
            'quantity' => '300 MT', 'offerPrice' => '1310',
            'created_by' => 7, 'status' => 2,
            'created_at' => '2026-10-23 09:00:00', 'updated_at' => '2026-10-23 09:00:00',
        ]);

        $sell = $this->actingAs(\App\User::find(1))->get(route('master.export.sell.queries.INR'))->assertOk();
        $sellCsv = $sell->streamedContent();
        $this->assertStringContainsString('ID,"Quality Type",Quality,"Quality Form",Grade,Packing,Quantity', $sellCsv);
        $this->assertStringContainsString('201,Basmati,1121,Steam,', $sellCsv);
        $this->assertStringContainsString('Black 12-14', $sellCsv);
        $this->assertStringContainsString('Karnal', $sellCsv);
        $this->assertStringContainsString('Simran,7777', $sellCsv);
        $this->assertStringContainsString('sample ready', $sellCsv);
        $this->assertStringContainsString('Pending', $sellCsv);

        $future = $this->actingAs(\App\User::find(1))->get(route('master.export.future.sell.queries.INR'))->assertOk();
        $futureCsv = $future->streamedContent();
        $this->assertStringContainsString('401,Basmati,1121,Steam,', $futureCsv);
        $this->assertStringContainsString('1310', $futureCsv);
        $this->assertStringContainsString('Moved to trade', $futureCsv);
    }

    public function test_export_pages_show_export_buttons(): void
    {
        $buy = $this->actingAs(\App\User::find(1))->get(route('master.list.buy.queries.INR'));
        $buy->assertOk();
        $this->assertStringContainsString((string) route('master.export.buy.queries.INR'), $buy->getContent());

        $sell = $this->actingAs(\App\User::find(1))->get(route('master.list.sell.queries.INR'));
        $sell->assertOk();
        $this->assertStringContainsString((string) route('master.export.sell.queries.INR'), $sell->getContent());
    }
}
