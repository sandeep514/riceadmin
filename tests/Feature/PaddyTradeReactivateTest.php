<?php

namespace Tests\Feature;

use App\PaddyTrade;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

/**
 * Regression: extending the expiry date (valid_days) on an Expired paddy
 * trade must reactivate it (2 -> 1). Previously the date saved but the
 * trade stayed Expired forever, because expirePastValidDayTrades() only
 * moves 1/4/12 -> 2 and updateTrade() never touches status.
 *
 * Uses the default sqlite :memory: connection from phpunit.xml.
 */
class PaddyTradeReactivateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('paddy_trades');
        Schema::create('paddy_trades', function (Blueprint $table) {
            $table->id();
            $table->integer('status')->default(1);
            $table->string('valid_days')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    private function makeTrade(int $status, ?string $validDays): PaddyTrade
    {
        return PaddyTrade::create([
            'status' => $status,
            'valid_days' => $validDays,
        ]);
    }

    public function test_expired_trade_with_future_expiry_reactivates(): void
    {
        $trade = $this->makeTrade(2, Carbon::now()->subDay()->format('Y-m-d H:i:s'));

        // Admin extends the expiry date into the future (what updateTrade saves).
        $trade->update(['valid_days' => Carbon::now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->assertTrue($trade->reactivateIfExpiryExtended());
        $this->assertSame(1, (int) $trade->fresh()->status);
    }

    public function test_expired_trade_with_still_past_expiry_stays_expired(): void
    {
        $trade = $this->makeTrade(2, Carbon::now()->subDays(5)->format('Y-m-d H:i:s'));

        $trade->update(['valid_days' => Carbon::now()->subDay()->format('Y-m-d H:i:s')]);

        $this->assertFalse($trade->reactivateIfExpiryExtended());
        $this->assertSame(2, (int) $trade->fresh()->status);
    }

    public function test_active_trade_is_untouched(): void
    {
        $trade = $this->makeTrade(1, Carbon::now()->addDays(10)->format('Y-m-d H:i:s'));

        $this->assertFalse($trade->reactivateIfExpiryExtended());
        $this->assertSame(1, (int) $trade->fresh()->status);
    }

    public function test_sold_and_deactivated_trades_are_never_reactivated(): void
    {
        foreach ([3, 5] as $status) {
            $trade = $this->makeTrade($status, Carbon::now()->addDays(10)->format('Y-m-d H:i:s'));

            $this->assertFalse($trade->reactivateIfExpiryExtended());
            $this->assertSame($status, (int) $trade->fresh()->status);
        }
    }

    public function test_unparseable_valid_days_leaves_status_unchanged(): void
    {
        $trade = $this->makeTrade(2, 'not-a-date');

        $this->assertFalse($trade->reactivateIfExpiryExtended());
        $this->assertSame(2, (int) $trade->fresh()->status);
    }

    public function test_reactivated_trade_is_not_immediately_re_expired(): void
    {
        $trade = $this->makeTrade(2, Carbon::now()->subDay()->format('Y-m-d H:i:s'));
        $trade->update(['valid_days' => Carbon::now()->addDays(10)->format('Y-m-d H:i:s')]);
        $this->assertTrue($trade->reactivateIfExpiryExtended());

        // The listing sweeper must leave the extended trade Active.
        PaddyTrade::expirePastValidDayTrades();

        $this->assertSame(1, (int) $trade->fresh()->status);
    }

    public function test_update_trade_controller_reactivates(): void
    {
        $src = file_get_contents(app_path('Http/Controllers/PaddySellQueryController.php'));
        $this->assertNotFalse($src);

        $start = strpos($src, 'public function updateTrade(');
        $this->assertNotFalse($start);
        $next = strpos($src, 'public function convertToTrade(', $start);
        $body = substr($src, $start, $next ? $next - $start : 5000);

        $this->assertStringContainsString(
            'reactivateIfExpiryExtended',
            $body,
            'updateTrade() must reactivate an Expired trade when expiry is extended'
        );
    }

    public function test_update_trade_status_controller_blocks_past_due_reactivation(): void
    {
        $src = file_get_contents(app_path('Http/Controllers/PaddySellQueryController.php'));
        $this->assertNotFalse($src);

        $start = strpos($src, 'public function updateTradeStatus(');
        $this->assertNotFalse($start);
        $next = strpos($src, 'public function close(', $start);
        $body = substr($src, $start, $next ? $next - $start : 5000);

        $this->assertStringContainsString(
            'expiryHasPassed',
            $body,
            'updateTradeStatus() must refuse Active/In-Process/Hold on past-due trades'
        );
    }

    public function test_expiry_has_passed(): void
    {
        $past = $this->makeTrade(2, Carbon::now()->subDay()->format('Y-m-d H:i:s'));
        $this->assertTrue($past->expiryHasPassed());

        $future = $this->makeTrade(2, Carbon::now()->addDay()->format('Y-m-d H:i:s'));
        $this->assertFalse($future->expiryHasPassed());

        $blank = $this->makeTrade(2, null);
        $this->assertFalse($blank->expiryHasPassed());

        $garbage = $this->makeTrade(2, 'not-a-date');
        $this->assertFalse($garbage->expiryHasPassed());
    }
}
