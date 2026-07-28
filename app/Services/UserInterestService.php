<?php

namespace App\Services;

use App\UserInterestedMap;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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

    /**
     * Active portal interest rows for a user (rice name, form, optional wand grade).
     *
     * @return array<int, array{rice_name_id: int, form_id: int, grade: int|null}>
     */
    public static function getActiveInterestTuplesForUser(int $userId): array
    {
        return UserInterestedMap::query()
            ->where('user_id', $userId)
            ->where('status', 1)
            ->get(['rice_name_id', 'form_id', 'grade'])
            ->map(function ($row) {
                $grade = $row->grade;
                if ($grade === null || $grade === '') {
                    return [
                        'rice_name_id' => (int) $row->rice_name_id,
                        'form_id' => (int) $row->form_id,
                        'grade' => null,
                    ];
                }

                return [
                    'rice_name_id' => (int) $row->rice_name_id,
                    'form_id' => (int) $row->form_id,
                    'grade' => (int) $grade,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Match trade against user interests (trade.quality = rice name, qualityForm = milestone3 form, grade = wand).
     * 3 = name + form + exact grade
     * 2 = name + form (interest has no grade, or interest grade differs from trade)
     * 0 = no match
     */
    public static function scoreTradeAgainstInterests(object $trade, array $tuples): int
    {
        if ($tuples === []) {
            return 0;
        }

        $quality = (int) ($trade->quality ?? 0);
        if ($quality <= 0) {
            return 0;
        }

        // Interests use rice_form_milestone3 ids — same as trade.qualityForm (not live-price form ids).
        $tradeFormId = (int) ($trade->qualityForm ?? 0);
        if ($tradeFormId <= 0) {
            return 0;
        }

        $tradeGrade = (int) ($trade->grade ?? 0);
        $best = 0;

        foreach ($tuples as $tuple) {
            if ($quality !== (int) $tuple['rice_name_id']) {
                continue;
            }
            if ($tradeFormId !== (int) $tuple['form_id']) {
                continue;
            }

            // Name + form always prefer this trade over unrelated qualities.
            $best = max($best, 2);

            if ($tuple['grade'] !== null && $tradeGrade > 0 && $tradeGrade === (int) $tuple['grade']) {
                $best = max($best, 3);
            }
        }

        return $best;
    }

    /**
     * Active trade statuses for web listing (Pending, In-Process, Active).
     */
    public static function webActiveTradeStatusIds(): array
    {
        return [1, 4, 6];
    }

    /**
     * Put active trades matching user interests first; preserve SQL order within each group.
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection  $trades
     * @return Collection
     */
    public static function orderTradesWithUserInterestsFirst($trades, int $userId): Collection
    {
        $collection = $trades instanceof Collection ? $trades : collect($trades);
        $tuples = self::getActiveInterestTuplesForUser($userId);

        if ($tuples === [] || $collection->isEmpty()) {
            return $collection->values();
        }

        $activeStatuses = self::webActiveTradeStatusIds();
        $scored = [];

        foreach ($collection->values() as $index => $trade) {
            $matchScore = self::scoreTradeAgainstInterests($trade, $tuples);
            $isActive = in_array((int) $trade->status, $activeStatuses, true);

            if ($isActive && $matchScore > 0) {
                $tier = 1;
            } elseif ($isActive) {
                $tier = 2;
            } else {
                $tier = 3;
            }

            $trade->setAttribute('interest_match_score', $matchScore);
            $trade->setAttribute('matches_user_interest', $matchScore > 0);

            $scored[] = [
                'trade' => $trade,
                'tier' => $tier,
                'score' => $matchScore,
                'index' => $index,
            ];
        }

        usort($scored, function ($a, $b) {
            if ($a['tier'] !== $b['tier']) {
                return $a['tier'] <=> $b['tier'];
            }
            if ($a['score'] !== $b['score']) {
                return $b['score'] <=> $a['score'];
            }

            return $a['index'] <=> $b['index'];
        });

        return collect(array_map(static fn (array $row) => $row['trade'], $scored));
    }

    /**
     * Web users with an active interest row matching rice name, form, and wand grade.
     *
     * @return \Illuminate\Support\Collection<int, \App\User>
     */
    public static function webUsersWithExactInterest(int $riceNameId, int $formId, int $gradeId)
    {
        if ($riceNameId <= 0 || $formId <= 0 || $gradeId <= 0) {
            return collect();
        }

        $userIds = UserInterestedMap::query()
            ->where('status', 1)
            ->where('rice_name_id', $riceNameId)
            ->where('form_id', $formId)
            ->where('grade', $gradeId)
            ->distinct()
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();

        if ($userIds === []) {
            return collect();
        }

        return \App\User::query()
            ->where('user_from', 'web')
            ->whereIn('id', $userIds)
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name', 'mobile', 'email']);
    }
}
