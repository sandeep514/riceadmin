<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BagVendors extends Model
{
    protected $table = "bag_vendor";
    protected $fillable = ['vendor_name','vendor_address','email','contact_person','contact_number','specialised','vendorType','status'];

    public static function vendorType (){
        return [
            1 => 'Rice Bag Suppliers',
            2 => 'Cartoon Suppliers ',
            3 => 'Cylinder Suppliers',
            4 => 'Domestic Transporters',
            5 => 'Clearing agents',
            6 => 'Forwarders',
            7 => 'Inspection Agencies',
            8 => 'Exports Bad Debts recover agencies'        
        ];
    }
}
