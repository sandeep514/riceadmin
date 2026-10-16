<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Regression: /api/web/prices/* caches the whole payload (including
 * livePriceStatusMessage currentState/message) under a versioned key.
 * Every admin mutation of price data OR market status must bump
 * web:prices:version, otherwise the API serves stale data — e.g. admin
 * re-opens the market but the API keeps responding "hold" until TTL expiry.
 */
class WebPricesCacheBustTest extends TestCase
{
    private function controllerMethodBody(string $method): string
    {
        $src = file_get_contents(app_path('Http/Controllers/LivePricesController.php'));
        $this->assertNotFalse($src);

        $start = strpos($src, 'public function ' . $method . '(');
        $this->assertNotFalse($start, $method . '() not found');

        // Body runs until the next public/private function or end of class.
        $nextPublic = strpos($src, 'public function ', $start + 10);
        $nextPrivate = strpos($src, 'private function ', $start + 10);
        $candidates = array_filter([$nextPublic, $nextPrivate], fn ($v) => $v !== false);
        $end = $candidates === [] ? strlen($src) : min($candidates);

        return substr($src, $start, $end - $start);
    }

    public function test_market_status_change_busts_web_prices_cache(): void
    {
        $body = $this->controllerMethodBody('updateMarketStatus');

        $this->assertStringContainsString(
            "Cache::increment('web:prices:version')",
            $body,
            'updateMarketStatus() must bust the web prices cache or currentState/message go stale'
        );
    }

    public function test_price_save_paths_bust_web_prices_cache(): void
    {
        foreach (['savePrice', 'savePriceSingle'] as $method) {
            $this->assertStringContainsString(
                "Cache::increment('web:prices:version')",
                $this->controllerMethodBody($method),
                $method . '() must bust the web prices cache'
            );
        }
    }

    public function test_price_delete_busts_web_prices_cache(): void
    {
        $this->assertStringContainsString(
            "Cache::increment('web:prices:version')",
            $this->controllerMethodBody('delete'),
            'delete() must bust the web prices cache'
        );
    }

    public function test_api_cache_key_is_versioned(): void
    {
        $src = file_get_contents(app_path('Http/Controllers/ApiController.php'));
        $this->assertNotFalse($src);

        $this->assertStringContainsString('web:prices:version', $src);
        $this->assertStringContainsString('web:prices:v', $src);
    }
}
