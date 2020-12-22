<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoadingReport extends Model
{
    protected $fillable = ['sntc_no','length','ad_mixture','sub_ad_mixture','moisture','kett','broken','dd','chalky',
        'brown_layer','stone','inmature','broken_pin','cooking'];
}
