<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PlanPageVisit extends Model
{
    protected $table = 'plan_page_visits';

    protected $fillable = [
        'user_id',
        'platform',
        'role_id',
        'category_id',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Record that an authenticated user opened the plans screen.
     */
    public static function recordForUser(
        int $userId,
        ?string $platform = null,
        ?int $roleId = null,
        ?int $categoryId = null
    ): ?self {
        if ($userId < 1 || ! Schema::hasTable('plan_page_visits')) {
            return null;
        }

        return self::create([
            'user_id' => $userId,
            'platform' => $platform ? strtolower(trim($platform)) : null,
            'role_id' => $roleId,
            'category_id' => $categoryId,
            'visited_at' => Carbon::now('Asia/Kolkata'),
        ]);
    }

    public static function recordFromRequest(Request $request, ?int $roleId = null, ?int $categoryId = null): ?self
    {
        $user = $request->user();
        if (! $user) {
            return null;
        }

        $platform = $request->attributes->get('auth_platform')
            ?? $request->header('X-Client-Platform')
            ?? $request->input('platform')
            ?? 'web';

        return self::recordForUser(
            (int) $user->id,
            is_string($platform) ? $platform : 'web',
            $roleId,
            $categoryId
        );
    }
}
