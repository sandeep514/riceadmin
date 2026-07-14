<?php

namespace App\Jobs;

use App\Services\TradeWebNotificationService;
use App\TradeQueriesINR;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTradeWebNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 3;

    public function __construct(
        public int $tradeId,
        public array $categoryIds,
        public bool $send,
        public string $audienceMode,
        public ?array $selectedUserIds,
        public string $title,
        public string $messageTemplate,
        public ?int $roleId = null
    ) {
    }

    public function handle(TradeWebNotificationService $svc): void
    {
        $trade = TradeQueriesINR::find($this->tradeId);
        if (! $trade) {
            Log::warning('SendTradeWebNotificationsJob: trade not found.', [
                'trade_id' => $this->tradeId,
            ]);

            return;
        }

        $svc->processTradeNotification(
            $trade,
            $this->categoryIds,
            $this->send,
            $this->audienceMode,
            $this->selectedUserIds,
            $this->title,
            $this->messageTemplate,
            $this->roleId
        );
    }
}
