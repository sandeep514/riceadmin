<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebUserNotification extends Model
{
    protected $table = 'web_notifications';

    protected $fillable = [
        'user_id',
        'notify_date',
        'title',
        'message',
        'role_id',
        'category_id',
        'audience_mode',
        'broadcast_group_id',
        'read_at',
        'is_cleared',
    ];

    protected $casts = [
        'notify_date' => 'date',
        'read_at' => 'datetime',
        'is_cleared' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
