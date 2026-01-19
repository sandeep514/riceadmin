<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebOtherServiceProvider extends Model
{
    protected $table = "web_other_service_provider";
    protected $fillable = ['category' ,'status'];

    public static function vendorType (){
        return [
            1 => 'Rice Bag Suppliers',
            2 => 'Cartoon Suppliers ',
            3 => 'Cylinder Suppliers',
            4 => 'Domestic Transporters',
            5 => 'Clearing agents',
            6 => 'Forwarders',
            7 => 'Inspection Agencies',
            8 => 'Exports Bad Debts recover agencies',   
            9 => 'Machinery Equipment',
            10 => 'Rice Lab Equipment Supplier',
            11 => 'Rice Sorters / Packing Services',
            12 => 'Warehouse Service',
            13 => 'Financing',
            14 => 'Resources'
        ];
    }
}
