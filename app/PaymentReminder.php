<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentReminder extends Model
{
    protected $fillable = ['date','buyer','seller','invoice_number','amount','rec_amount','balance_amount','user_id'];
}
