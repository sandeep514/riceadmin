<?php

namespace App\Services;

use App\UserInterestedMap;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserInterestService
{
    /**
     * Normalize wand / grade ids from portal or admin form (same rules as legacy portal API).
     *
     * @param  mixed  $grades
     * @return array<int|null>
     */
    public static function normalizeGradeIds($grades): array
    {
        if ($grades === null || $grades === '' || $grades === []) {
            return [null];
        }

        if (! is_array($grades)) {
            $grades = [$grades];
        }

        $normalized = [];
        foreach ($grades as $value) {
            if (is_array($value) || $value === null || $value === '') {
                continue;
            }
            if (! is_numeric($value)) {
                continue;
            }
            $normalized[] = (int) $value;
        }

        $normalized = array_values(array_unique(array_filter($normalized, function ($id) {
            return $id > 0;
        })));

        return ! empty($normalized) ? $normalized : [null];
    }

    /**
     * Replace all interest rows for a user. Pass empty array to clear.
     *
     * @param  array<int, array{name_id?:int, form_id?:int, grades?:mixed}>  $interestedItems
     */
    public static function syncForUser(int $userId, array $interestedItems): int
    {
        $now = Carbon::now(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d H:i:s');
        $rows = [];

        foreach ($interestedItems as $item) {
            if (! isset($item['name_id'], $item['form_id'])) {
                continue;
            }
            $nameId = (int) $item['name_id'];
            $formId = (int) $item['form_id'];
            if ($nameId <= 0 || $formId <= 0) {
                continue;
            }

            foreach (self::normalizeGradeIds($item['grades'] ?? null) as $gradeId) {
                $rows[] = [
                    'user_id' => $userId,
                    'rice_name_id' => $nameId,
                    'form_id' => $formId,
                    'grade' => $gradeId,
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::transaction(function () use ($userId, $rows) {
            UserInterestedMap::where('user_id', $userId)->delete();
            if (! empty($rows)) {
                UserInterestedMap::insert($rows);
            }
        });

        return count($rows);
    }
}
