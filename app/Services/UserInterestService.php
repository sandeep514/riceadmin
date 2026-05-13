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

    /**
     * Admin: add only new (rice_name_id, form_id, grade) rows; keep everything already saved.
     * Use table "Delete" to remove rows; portal API still uses {@see syncForUser} full replace.
     *
     * @param  array<int, array{name_id?:int, form_id?:int, grades?:mixed}>  $interestedItems
     */
    public static function appendUniqueInterestsForUser(int $userId, array $interestedItems): int
    {
        $now = Carbon::now(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d H:i:s');

        $tupleKey = static function (int $nameId, int $formId, $grade): string {
            $g = $grade === null || $grade === '' ? "\0null" : (string) (int) $grade;

            return $nameId.'|'.$formId.'|'.$g;
        };

        $desired = [];
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
                $key = $tupleKey($nameId, $formId, $gradeId);
                $desired[$key] = [
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

        if ($desired === []) {
            return 0;
        }

        $existingKeys = [];
        foreach (UserInterestedMap::query()->where('user_id', $userId)->get(['rice_name_id', 'form_id', 'grade']) as $row) {
            $existingKeys[$tupleKey((int) $row->rice_name_id, (int) $row->form_id, $row->grade)] = true;
        }

        $toInsert = [];
        foreach ($desired as $key => $payload) {
            if (! isset($existingKeys[$key])) {
                $toInsert[] = $payload;
                $existingKeys[$key] = true;
            }
        }

        if ($toInsert !== []) {
            UserInterestedMap::insert($toInsert);
        }

        return count($toInsert);
    }
}
