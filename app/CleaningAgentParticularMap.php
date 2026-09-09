<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CleaningAgentParticularMap extends Model
{
    protected $table = 'cleaning_agent_particulars_map';

    protected $fillable = [
        'product_id',
        'particular_id',
        'particular_name',
        'rate',
        'is_other',
        'sort_order',
    ];

    protected $casts = [
        'particular_id' => 'integer',
        'is_other' => 'integer',
        'sort_order' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(WebCleaningAgentProduct::class, 'product_id', 'id');
    }

    public function particular()
    {
        return $this->belongsTo(VendorContainerParticular::class, 'particular_id', 'id');
    }
}
