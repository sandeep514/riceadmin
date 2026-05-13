<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserInterestedMap extends Model
{
    protected $table = 'user_interested_map_table';

    protected $fillable = [
        'user_id',
        'rice_name_id',
        'form_id',
        'grade',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function riceName()
    {
        return $this->belongsTo(RiceName::class, 'rice_name_id');
    }

    public function riceForm()
    {
        return $this->belongsTo(RiceFormMilestone3::class, 'form_id');
    }

    /**
     * Wand (grade) selected for this interest row.
     */
    public function wandGrade()
    {
        return $this->belongsTo(WandModel::class, 'grade');
    }
}
