<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LivePrice extends Model
{
    protected $table = 'live_prices';
    protected $fillable = ['name', 'form', 'cropGrade', 'cropYear', 'min_price', 'max_price', 'state', 'up_down', 'state_order','opening','closing','monthStart','monthEnd','is_updated_by_admin', 'status'];

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

    
}
