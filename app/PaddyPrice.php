<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\PaddyMandiModel;
use App\PaddyStateModel;
use App\RiceName;


class PaddyPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'mandi',
        'state',
        'quality_id',
        'crop_year',
        'hand_cutting_price',
        'machine_cutting_price',
        'moisture',
        'total_arrivals',
        'change',
        'status'
    ];
    protected $table = "paddy_prices";
    protected $attributes = [
        'change' => 'stable',
        'status' => 1,
    ];

    public function getMandi_rel()
    {
        return $this->belongsTo(PaddyMandiModel::class , 'mandi' , 'id');
    }
    public function getState_rel()
    {
        return $this->belongsTo(PaddyStateModel::class , 'state' , 'id');
    }
    public function quality_rel()
    {
        return $this->belongsTo(RiceName::class , 'quality_id' , 'id');
    }




}