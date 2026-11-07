<?php

namespace Tests\Feature;

use App\Http\Controllers\PaddyApiController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaddyPricesGraphTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('paddy_prices');
        Schema::dropIfExists('paddyMandi');
        Schema::dropIfExists('paddyStates');
        Schema::dropIfExists('paddy_qualities');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $t) {
            $t->increments('id');
            $t->string('api_token')->nullable();
            $t->string('mobile_api_token')->nullable();
            $t->tinyInteger('userType')->nullable();
            $t->string('user_from')->nullable();
            $t->tinyInteger('status')->default(1);
            $t->tinyInteger('is_active_by_admin')->default(1);
            $t->tinyInteger('is_deactivated')->default(0);
            $t->timestamps();
        });
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
        DB::table('paddyMandi')->insert([
            ['id' => 10, 'mandi' => 'KARNAL', 'state_id' => 1, 'status' => 1],
            ['id' => 11, 'mandi' => 'AMBALA', 'state_id' => 1, 'status' => 1],
        ]);
        DB::table('paddy_qualities')->insert([
            ['id' => 1, 'type' => 'basmati', 'quality' => '1121', 'order' => 1, 'status' => 1],
        ]);
    }

    public function test_graph_aggregates_mandis_for_tooltip_and_series(): void
    {
        $day = now('Asia/Kolkata')->format('Y-m-d');

        DB::table('paddy_prices')->insert([
            [
                'mandi' => 10,
                'state' => 1,
                'quality_id' => 1,
                'crop_year' => 2026,
                'min_hand_cutting_price' => '----',
                'hand_cutting_price' => '3000',
                'min_machine_cutting_price' => '----',
                'machine_cutting_price' => '2800',
                'status' => 1,
                'created_at' => $day.' 10:00:00',
                'updated_at' => $day.' 10:00:00',
            ],
            [
                'mandi' => 11,
                'state' => 1,
                'quality_id' => 1,
                'crop_year' => 2026,
                'min_hand_cutting_price' => '----',
                'hand_cutting_price' => '3400',
                'min_machine_cutting_price' => '----',
                'machine_cutting_price' => '3200',
                'status' => 1,
                'created_at' => $day.' 11:00:00',
                'updated_at' => $day.' 11:00:00',
            ],
        ]);

        $request = Request::create('/api/paddy-prices/graph', 'GET', [
            'qualityId' => 1,
            'days' => 60,
        ]);

        $response = (new PaddyApiController)->getPaddyPricesGraph($request);
        $payload = $response->getData(true);

        $this->assertTrue($payload['status']);
        $this->assertSame([$day], $payload['dates']);
        $this->assertSame([3200], $payload['handCutting']); // avg 3000+3400
        $this->assertSame([3000], $payload['machineCutting']); // avg 2800+3200
        $this->assertCount(1, $payload['data']);
        $this->assertSame(2, $payload['data'][0]['mandiCount']);
        $this->assertEqualsCanonicalizing(
            ['AMBALA', 'KARNAL'],
            array_column($payload['data'][0]['mandis'], 'mandiName')
        );
    }
}
