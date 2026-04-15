<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $table = 'job_applications';

    protected $fillable = [
        'posted_job_id',
        'name',
        'email',
        'mobile',
        'experience',
        'cv_file',
        'status',
    ];

    public function postedJob()
    {
        return $this->belongsTo(PostedJob::class, 'posted_job_id', 'id');
    }
}
