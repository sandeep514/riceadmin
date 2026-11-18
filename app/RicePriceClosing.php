<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class RicePriceClosing extends Model
{
    public const MARKET_LIVE = 'live';
    public const MARKET_RICE_SOURCING = 'rice_sourcing';
    public const MARKET_PADDY = 'paddy';

    public const MARKETS = [
        self::MARKET_LIVE => 'Live prices',
        self::MARKET_RICE_SOURCING => 'Rice sourcing',
        self::MARKET_PADDY => 'Paddy pricing',
    ];

    protected $table = 'rice_price_closings';

    protected $fillable = [
        'market',
        'close_date',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'close_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function normalizeMarket(?string $market): string
    {
        $market = strtolower(trim((string) $market));
        if (array_key_exists($market, self::MARKETS)) {
            return $market;
        }

        return self::MARKET_LIVE;
    }

    public static function marketLabel(string $market): string
    {
        $market = self::normalizeMarket($market);

        return self::MARKETS[$market] ?? 'Live prices';
    }

    public static function forDate($date, string $market = self::MARKET_LIVE): ?self
    {
        if (! Schema::hasTable('rice_price_closings')) {
            return null;
        }

        $market = self::normalizeMarket($market);
        $day = $date instanceof Carbon
            ? $date->copy()->timezone('Asia/Kolkata')->toDateString()
            : Carbon::parse($date, 'Asia/Kolkata')->toDateString();

        $query = self::query()->whereDate('close_date', $day);
        if (Schema::hasColumn('rice_price_closings', 'market')) {
            $query->where('market', $market);
        }

        return $query->first();
    }

    /**
     * Upcoming closings from today (IST), optionally limited.
     */
    public static function upcoming(?int $limit = null, string $market = self::MARKET_LIVE): Collection
    {
        if (! Schema::hasTable('rice_price_closings')) {
            return collect();
        }

        $market = self::normalizeMarket($market);
        $today = Carbon::now('Asia/Kolkata')->toDateString();

        $query = self::query()
            ->whereDate('close_date', '>=', $today)
            ->orderBy('close_date');

        if (Schema::hasColumn('rice_price_closings', 'market')) {
            $query->where('market', $market);
        }

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Group consecutive same-reason dates into ranges.
     *
     * @return array<int, array{from:string,to:string,reason:string,message:string,display_from:string,display_to:string}>
     */
    public static function contiguousBlocks(?Carbon $fromDate = null, string $market = self::MARKET_LIVE): array
    {
        if (! Schema::hasTable('rice_price_closings')) {
            return [];
        }

        $market = self::normalizeMarket($market);
        $query = self::query()->orderBy('close_date');
        if (Schema::hasColumn('rice_price_closings', 'market')) {
            $query->where('market', $market);
        }
        if ($fromDate) {
            $query->whereDate('close_date', '>=', $fromDate->copy()->timezone('Asia/Kolkata')->toDateString());
        }

        $rows = $query->get();
        $blocks = [];

        foreach ($rows as $row) {
            if (! $row->close_date) {
                continue;
            }
            $date = $row->close_date->toDateString();
            $reason = trim((string) $row->reason);

            if ($blocks === []) {
                $blocks[] = self::makeBlock($date, $date, $reason);
                continue;
            }

            $lastIndex = count($blocks) - 1;
            $last = $blocks[$lastIndex];
            $expectedNext = Carbon::parse($last['to'], 'Asia/Kolkata')->addDay()->toDateString();

            if ($date === $expectedNext && $reason === $last['reason']) {
                $blocks[$lastIndex] = self::makeBlock($last['from'], $date, $reason);
            } else {
                $blocks[] = self::makeBlock($date, $date, $reason);
            }
        }

        return $blocks;
    }

    /**
     * Notice to show after daily market close / on closing days.
     */
    public static function noticeFor(?Carbon $at = null, ?string $marketTo = null, string $market = self::MARKET_LIVE): ?array
    {
        $now = $at ? $at->copy()->timezone('Asia/Kolkata') : Carbon::now('Asia/Kolkata');
        $today = $now->toDateString();
        $tomorrow = $now->copy()->addDay()->toDateString();
        $marketTo = $marketTo ?: '18:00';
        $afterMarketClose = $now->format('H:i') > $marketTo;

        $blocks = self::contiguousBlocks($now->copy()->subDays(1)->startOfDay(), $market);
        if ($blocks === []) {
            return null;
        }

        $blockContaining = function (string $day) use ($blocks): ?array {
            foreach ($blocks as $block) {
                if ($day >= $block['from'] && $day <= $block['to']) {
                    return $block;
                }
            }

            return null;
        };

        $firstBlockFrom = function (string $day) use ($blocks): ?array {
            foreach ($blocks as $block) {
                if ($block['from'] >= $day) {
                    return $block;
                }
            }

            return null;
        };

        $current = $blockContaining($today);

        if ($afterMarketClose) {
            $next = $firstBlockFrom($tomorrow);
            if ($next) {
                return $next;
            }
        }

        if ($current) {
            return $current;
        }

        return null;
    }

    /**
     * @return array{from:string,to:string,reason:string,message:string,display_from:string,display_to:string,is_range:bool}
     */
    private static function makeBlock(string $from, string $to, string $reason): array
    {
        $fromLabel = Carbon::parse($from, 'Asia/Kolkata')->format('d M Y');
        $toLabel = Carbon::parse($to, 'Asia/Kolkata')->format('d M Y');
        $isRange = $from !== $to;
        $reasonPart = $reason !== '' ? ' due to '.$reason : '';

        if ($isRange) {
            $message = 'Market closed from '.$fromLabel.' to '.$toLabel.$reasonPart;
        } else {
            $message = 'Market closed on '.$fromLabel.$reasonPart;
        }

        return [
            'from' => $from,
            'to' => $to,
            'reason' => $reason,
            'display_from' => $fromLabel,
            'display_to' => $toLabel,
            'is_range' => $isRange,
            'message' => $message,
        ];
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'market' => Schema::hasColumn('rice_price_closings', 'market')
                ? self::normalizeMarket((string) ($this->market ?? self::MARKET_LIVE))
                : self::MARKET_LIVE,
            'date' => $this->close_date ? $this->close_date->format('Y-m-d') : null,
            'close_date' => $this->close_date ? $this->close_date->format('Y-m-d') : null,
            'reason' => (string) $this->reason,
            'display_date' => $this->close_date ? $this->close_date->format('d M Y') : null,
        ];
    }
}
