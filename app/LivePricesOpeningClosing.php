<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LivePricesOpeningClosing extends Model
{

    protected $table = 'live_price_closing';
    protected $fillable = ['trade_for','farming_type','name','form','cropYear','state','opening','closing','status'];

    public function name_rel()
    {
        return $this->belongsTo(RiceName::class, 'name', 'id')->orderBy('order', 'ASC');
    }

    public function form_rel()
    {
        return $this->belongsTo(RiceForm::class, 'form', 'id')->where('status', 1);
    }

   
    
}
