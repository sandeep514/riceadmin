<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SqlQueryLog extends Model
{
    public $timestamps = false;

    protected $table = 'sql_query_logs';

    protected $fillable = [
        'source',
        'duration_ms',
        'cpu_percent',
        'sql_hash',
        'sql_text',
        'db_name',
        'connection',
        'request_path',
        'mysql_thread_id',
        'created_at',
    ];

    protected $casts = [
        'duration_ms' => 'integer',
        'cpu_percent' => 'integer',
        'mysql_thread_id' => 'integer',
        'created_at' => 'datetime',
    ];
}
