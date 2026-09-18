<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LivePrice extends Model
{
    public const TIMEZONE = 'Asia/Kolkata';

    protected $table = 'live_prices';
    protected $fillable = ['name', 'form', 'cropGrade', 'cropYear', 'min_price', 'max_price', 'state', 'up_down', 'state_order','opening','closing','monthStart','monthEnd','is_updated_by_admin', 'status', 'created_at', 'updated_at'];

    public function freshTimestamp()
    {
        return Carbon::now(self::TIMEZONE);
    }

    public function name_rel()
    {
        return $this->belongsTo(RiceName::class, 'name', 'id')->orderBy('order', 'ASC');
    }

    public function form_rel()
    {
        return $this->belongsTo(RiceForm::class, 'form', 'id')->where('status', 1);
    }

    public function lastWeekRecord()
    {
        return $this->hasOne(LivePrice::class, 'id')
            ->where('name', $this->name)
            ->where('state', $this->state)
            ->where('cropGrade', $this->cropGrade);
        // ->whereDate('created_at', $this->created_at->subDays(7)->format('Y-m-d'));
    }
    
    public function trades()
    {
        return $this->hasMany(TradeQueriesINR::class, 'quality', 'name')
            ->whereColumn('trade_query_milestone3.qualityFormLinkWithLivePrice', $this->getTable().'.form')->whereColumn('trade_query_milestone3.stateLinkWithLivePrice' , $this->getTable().'.state')->whereIn('status' , [1,6,4]);
            // ->whereColumn('trade_query_milestone3.qualityForm', $this->getTable().'.form')->whereIn('status' , [1,6,4]);
            // ->whereColumn('trade_query_milestone3.qualityForm', $this->getTable().'.form')->whereIn('status' , [1,6,4,3]);
    }

    public function closingRel()
    {
        return $this->hasOne(LivePrice::class, 'name', 'name')
            ->whereColumn('form', 'live_prices.form')
            ->whereColumn('state', 'live_prices.state')
            ->whereColumn('cropYear', 'live_prices.cropYear')
            ->whereNotNull('closing')
            ->where('closing', '!=', '')
            ->latest('created_at')
            ->select('id', 'name', 'form', 'state', 'cropYear', 'closing', 'created_at');
    }

    public function openingRel()
    {
        return $this->hasOne(LivePrice::class, 'name', 'name')
            ->whereColumn('form', 'live_prices.form')
            ->whereColumn('state', 'live_prices.state')
            ->whereColumn('cropYear', 'live_prices.cropYear')
            ->whereNotNull('opening')
            ->where('opening', '!=', '')
            ->oldest('created_at')
            ->select('id', 'name', 'form', 'state', 'cropYear', 'opening', 'created_at');
    }

    public static function dayStart($date): Carbon
    {
        if ($date instanceof \DateTimeInterface) {
            return Carbon::instance($date)->timezone(self::TIMEZONE)->startOfDay();
        }

        return Carbon::parse((string) $date, self::TIMEZONE)->timezone(self::TIMEZONE)->startOfDay();
    }

    /**
     * IST wall-clock for MySQL DATETIME compares (no UTC shift).
     */
    public static function istDateTime($date): string
    {
        if ($date instanceof \DateTimeInterface) {
            return Carbon::instance($date)->timezone(self::TIMEZONE)->format('Y-m-d H:i:s');
        }

        return Carbon::parse((string) $date, self::TIMEZONE)->timezone(self::TIMEZONE)->format('Y-m-d H:i:s');
    }

    /**
     * Half-open IST day: created_at >= 'Y-m-d 00:00:00' AND created_at < next day.
     *
     * @return array{0: string, 1: string}
     */
    public static function createdAtDayRange($date): array
    {
        $start = self::dayStart($date);

        return [self::istDateTime($start), self::istDateTime($start->copy()->addDay())];
    }

    public static function applyCreatedAtDay($query, $date, string $column = 'created_at')
    {
        [$start, $next] = self::createdAtDayRange($date);

        return $query->where($column, '>=', $start)->where($column, '<', $next);
    }

    public function scopeOnCreatedDay($query, $date)
    {
        return self::applyCreatedAtDay($query, $date);
    }

    public function scopeCreatedBeforeDay($query, $date)
    {
        return $query->where('created_at', '<', self::istDateTime(self::dayStart($date)));
    }

    public function scopeCreatedOnOrBeforeDay($query, $date)
    {
        return $query->where('created_at', '<', self::istDateTime(self::dayStart($date)->addDay()));
    }

    public function scopeCreatedAfterDay($query, $date)
    {
        return $query->where('created_at', '>=', self::istDateTime(self::dayStart($date)->addDay()));
    }

    public function scopeCreatedFromDay($query, $date)
    {
        return $query->where('created_at', '>=', self::istDateTime(self::dayStart($date)));
    }

    public function scopeCreatedBetweenDays($query, $startDate, $endDate)
    {
        return $query
            ->where('created_at', '>=', self::istDateTime(self::dayStart($startDate)))
            ->where('created_at', '<', self::istDateTime(self::dayStart($endDate)->addDay()));
    }
}
