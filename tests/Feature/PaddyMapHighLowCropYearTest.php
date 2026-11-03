<?php

namespace Tests\Feature;

use App\PaddyPrice;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaddyMapHighLowCropYearTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('paddy_prices');
        Schema::create('paddy_prices', function (Blueprint $table) {
            $table->id();
            $table->integer('mandi');
            $table->integer('state');
            $table->integer('quality_id');
            $table->integer('crop_year')->nullable();
            $table->string('min_hand_cutting_price', 256)->nullable();
            $table->string('hand_cutting_price', 256)->nullable();
            $table->string('min_machine_cutting_price', 256)->nullable();
            $table->string('machine_cutting_price', 256)->nullable();
            $table->string('moisture', 256)->nullable();
            $table->string('total_arrivals', 256)->nullable();
            $table->string('change', 256)->default('stable');
            $table->boolean('status')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    private function newPrice(array $attrs): PaddyPrice
    {
        $timestamps = [];
        foreach (['created_at', 'updated_at'] as $key) {
            if (array_key_exists($key, $attrs)) {
                $timestamps[$key] = $attrs[$key];
                unset($attrs[$key]);
            }
        }
        $row = new PaddyPrice($attrs);
        foreach ($timestamps as $key => $value) {
            $row->{$key} = $value;
        }
        $row->save();

        return $row;
    }

    private function seedYears(): void
    {
        $tz = config('app.timezone', 'Asia/Kolkata');

        $this->newPrice([
            'mandi' => 10,
            'state' => 1,
            'quality_id' => 5,
            'crop_year' => 2025,
            'hand_cutting_price' => '1000',
            'machine_cutting_price' => '1100',
            'created_at' => Carbon::createFromFormat('Y-m-d', '2025-11-01', $tz)->startOfDay(),
            'updated_at' => Carbon::createFromFormat('Y-m-d', '2025-11-01', $tz)->startOfDay(),
        ]);
        $this->newPrice([
            'mandi' => 10,
            'state' => 1,
            'quality_id' => 5,
            'crop_year' => 2025,
            'hand_cutting_price' => '2500',
            'machine_cutting_price' => '2600',
            'created_at' => Carbon::createFromFormat('Y-m-d', '2025-12-01', $tz)->startOfDay(),
            'updated_at' => Carbon::createFromFormat('Y-m-d', '2025-12-01', $tz)->startOfDay(),
        ]);
        $this->newPrice([
            'mandi' => 10,
            'state' => 1,
            'quality_id' => 5,
            'crop_year' => 2026,
            'hand_cutting_price' => '1800',
            'machine_cutting_price' => '1850',
            'created_at' => Carbon::createFromFormat('Y-m-d', '2026-10-01', $tz)->startOfDay(),
            'updated_at' => Carbon::createFromFormat('Y-m-d', '2026-10-01', $tz)->startOfDay(),
        ]);
        $this->newPrice([
            'mandi' => 10,
            'state' => 1,
            'quality_id' => 5,
            'crop_year' => 2026,
            'hand_cutting_price' => '2000',
            'machine_cutting_price' => '2100',
            'created_at' => Carbon::createFromFormat('Y-m-d', '2026-11-01', $tz)->startOfDay(),
            'updated_at' => Carbon::createFromFormat('Y-m-d', '2026-11-01', $tz)->startOfDay(),
        ]);
    }

    public function test_high_low_uses_requested_crop_year_only(): void
    {
        $this->seedYears();

        $response = $this->getJson('/api/get/paddy/map/data/10/1/5?cropYear=2026');

        $response->assertOk()
            ->assertJsonPath('crop_year', '2026')
            ->assertJsonPath('handCutting.lowvalue', 1800)
            ->assertJsonPath('handCutting.highvalue', 2000)
            ->assertJsonPath('machineCutting.lowvalue', 1850)
            ->assertJsonPath('machineCutting.highvalue', 2100);
    }

    public function test_omitted_crop_year_defaults_to_latest_year(): void
    {
        $this->seedYears();

        $response = $this->getJson('/api/get/paddy/map/data/10/1/5');

        $response->assertOk()
            ->assertJsonPath('crop_year', '2026')
            ->assertJsonPath('handCutting.lowvalue', 1800)
            ->assertJsonPath('handCutting.highvalue', 2000);
    }
}
