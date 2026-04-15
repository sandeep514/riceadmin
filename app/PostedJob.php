<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostedJob extends Model
{
    protected $table = 'posted_jobs';

    public const EMPLOYMENT_FULLTIME = 'fulltime';

    public const EMPLOYMENT_PARTTIME = 'parttime';

    /** Published / visible on public API and listings */
    public const STATUS_ACTIVE = 1;

    /** Hidden from public API; admin can re-activate */
    public const STATUS_INACTIVE = 0;

    protected $fillable = [
        'title',
        'description',
        'job_role',
        'location',
        'employment_type',
        'last_date_apply',
        'number_of_positions',
        'status',
    ];

    protected $casts = [
        'last_date_apply' => 'date',
    ];

    public static function employmentTypeOptions(): array
    {
        return [
            self::EMPLOYMENT_FULLTIME => 'Full time',
            self::EMPLOYMENT_PARTTIME => 'Part time',
        ];
    }
}
