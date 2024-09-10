<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    protected $fillable = ['banner' , 'state','route','price','state_order'];
    public function PortAttachments()
    {
        return $this->belongsTo(PortImages::class , 'state','port' );
    }
}