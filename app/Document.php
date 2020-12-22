<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = ['date','contract_no','truck_no','driver_no','contract_copy','bill_copy','bilty_copy',
        'kanta_parchi','due_days','due_date','user_id'];
}
