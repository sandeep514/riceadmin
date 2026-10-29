<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class RicePriceClosing extends Model
{
    protected $table = 'rice_price_closings';

    protected $fillable = [
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

    public static function forDate($date): ?self
    {
        if (! Schema::hasTable('rice_price_closings')) {
            return null;
        }

        $day = $date instanceof Carbon
            ? $date->copy()->timezone('Asia/Kolkata')->toDateString()
            : Carbon::parse($date, 'Asia/Kolkata')->toDateString();

        return self::query()->whereDate('close_date', $day)->first();
    }

    /**
     * Upcoming closings from today (IST), optionally limited.
     */
    public static function upcoming(?int $limit = null): Collection
    {
        if (! Schema::hasTable('rice_price_closings')) {
            return collect();
        }

        $today = Carbon::now('Asia/Kolkata')->toDateString();

        $query = self::query()
            ->whereDate('close_date', '>=', $today)
            ->orderBy('close_date');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Group consecutive same-reason dates into ranges.
     * e.g. 30 Oct–2 Nov Strike, then 3–5 Nov Diwali as separate blocks.
     *
     * @return array<int, array{from:string,to:string,reason:string,message:string,display_from:string,display_to:string}>
     */
    public static function contiguousBlocks(?Carbon $fromDate = null): array
    {
        if (! Schema::hasTable('rice_price_closings')) {
            return [];
        }

        $query = self::query()->orderBy('close_date');
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

            // Extend only when next calendar day AND same reason.
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
     *
     * - After market_to on 29th → next block starting tomorrow (30–2 strike)
     * - During 30–2 → current strike block
     * - After market_to on 2nd → next block (3–5 Diwali)
     */
    public static function noticeFor(?Carbon $at = null, ?string $marketTo = null): ?array
    {
        $now = $at ? $at->copy()->timezone('Asia/Kolkata') : Carbon::now('Asia/Kolkata');
        $today = $now->toDateString();
        $tomorrow = $now->copy()->addDay()->toDateString();
        $marketTo = $marketTo ?: '18:00';
        $afterMarketClose = $now->format('H:i') > $marketTo;

        $blocks = self::contiguousBlocks($now->copy()->subDays(1)->startOfDay());
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

        // After daily close: preview the next block that starts tomorrow or later.
        // (On last day of a range after 6pm, this surfaces the following festival/strike.)
        if ($afterMarketClose) {
            $next = $firstBlockFrom($tomorrow);
            if ($next) {
                return $next;
            }
        }

        // During a special closing day (before/at market close): show that range.
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
            'date' => $this->close_date ? $this->close_date->format('Y-m-d') : null,
            'close_date' => $this->close_date ? $this->close_date->format('Y-m-d') : null,
            'reason' => (string) $this->reason,
            'display_date' => $this->close_date ? $this->close_date->format('d M Y') : null,
        ];
    }
}
