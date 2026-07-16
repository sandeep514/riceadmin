<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebPersonalDetails extends Model
{
    protected $table = 'web_personal_details';
    protected $fillable = [
        'user_id',
        'firstname',
        'lastname',
        'avatar',
        'email',
        'phone_number',
        'state',
        'district',
        'address',
        'farmer_unique_id',
        'pan_card',
        'status',
    ];

    /**
     * State master (when `state` stores web_states.id).
     */
    public function stateRel()
    {
        return $this->belongsTo(WebStates::class, 'state', 'id');
    }

    /**
     * District/city master (when `district` stores web_cities.id).
     */
    public function districtRel()
    {
        return $this->belongsTo(WebCities::class, 'district', 'id');
    }
}
