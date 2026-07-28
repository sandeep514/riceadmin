<?php

namespace App\Services;

use App\RiceFormMilestone3;
use App\RiceFormParentMap;
use App\RiceName;
use App\UserInterestedMap;
use App\WandModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserInterestService
{
    /** @var array<int, string> */
    private static array $formNameCache = [];

    /** @var array<int, string> */
    private static array $wandValueCache = [];

    /** @var array<int, string> */
    private static array $riceNameCache = [];

    /** @var array<int, array<int>>|null parent_form_id => child ids, plus reverse child => parents */
    private static ?array $formParentChildIndex = null;
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
     * 3 = name + form + exact grade (id or same wand value)
     * 2 = name + form
     * 1 = name only (Preferred rice quality, e.g. any PR-11/14)
     * 0 = no match
     *
     * Form match: same qualityForm id, same form name, or parent/child form map.
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

        $tradeFormId = (int) ($trade->qualityForm ?? 0);
        $tradeGrade = (int) ($trade->grade ?? 0);
        $tradeRiceLabel = self::cachedRiceNameLabel($quality);
        $tradeFormLabel = $tradeFormId > 0 ? self::cachedFormNameLabel($tradeFormId) : '';
        $tradeWandLabel = $tradeGrade > 0 ? self::cachedWandValueLabel($tradeGrade) : '';
        $best = 0;

        foreach ($tuples as $tuple) {
            $interestNameId = (int) $tuple['rice_name_id'];
            $interestFormId = (int) $tuple['form_id'];
            $interestGrade = $tuple['grade'] !== null ? (int) $tuple['grade'] : null;

            if (! self::riceNamesMatch($quality, $interestNameId, $tradeRiceLabel)) {
                continue;
            }

            // Name alone → Preferred over unrelated qualities (e.g. 1509).
            $best = max($best, 1);

            if (! self::formsMatch($tradeFormId, $interestFormId, $tradeFormLabel)) {
                continue;
            }

            // Name + form.
            $best = max($best, 2);

            if ($interestGrade !== null && $interestGrade > 0 && self::gradesMatch($tradeGrade, $interestGrade, $tradeWandLabel)) {
                $best = max($best, 3);
            }
        }

        return $best;
    }

    private static function normalizeLabel(?string $value): string
    {
        $value = strtolower(trim((string) $value));
        $value = str_replace(['/', '_'], '-', $value);
        $value = preg_replace('/\s+/', '', $value) ?? '';

        return $value;
    }

    private static function cachedRiceNameLabel(int $id): string
    {
        if ($id <= 0) {
            return '';
        }
        if (! array_key_exists($id, self::$riceNameCache)) {
            self::$riceNameCache[$id] = self::normalizeLabel(
                (string) (RiceName::query()->where('id', $id)->value('name') ?? '')
            );
        }

        return self::$riceNameCache[$id];
    }

    private static function cachedFormNameLabel(int $id): string
    {
        if ($id <= 0) {
            return '';
        }
        if (! array_key_exists($id, self::$formNameCache)) {
            self::$formNameCache[$id] = self::normalizeLabel(
                (string) (RiceFormMilestone3::query()->where('id', $id)->value('name') ?? '')
            );
        }

        return self::$formNameCache[$id];
    }

    private static function cachedWandValueLabel(int $id): string
    {
        if ($id <= 0) {
            return '';
        }
        if (! array_key_exists($id, self::$wandValueCache)) {
            self::$wandValueCache[$id] = self::normalizeLabel(
                (string) (WandModel::query()->where('id', $id)->value('value') ?? '')
            );
        }

        return self::$wandValueCache[$id];
    }

    private static function riceNamesMatch(int $tradeQualityId, int $interestNameId, string $tradeRiceLabel): bool
    {
        if ($tradeQualityId === $interestNameId) {
            return true;
        }

        $interestLabel = self::cachedRiceNameLabel($interestNameId);

        return $tradeRiceLabel !== '' && $interestLabel !== '' && $tradeRiceLabel === $interestLabel;
    }

    private static function formsMatch(int $tradeFormId, int $interestFormId, string $tradeFormLabel): bool
    {
        if ($interestFormId <= 0) {
            return false;
        }

        if ($tradeFormId > 0 && $tradeFormId === $interestFormId) {
            return true;
        }

        if ($tradeFormId > 0 && self::formsLinkedByParentMap($tradeFormId, $interestFormId)) {
            return true;
        }

        if ($tradeFormId > 0) {
            $interestLabel = self::cachedFormNameLabel($interestFormId);
            if ($tradeFormLabel !== '' && $interestLabel !== '' && $tradeFormLabel === $interestLabel) {
                return true;
            }
        }

        return false;
    }

    private static function formsLinkedByParentMap(int $formA, int $formB): bool
    {
        self::bootFormParentChildIndex();
        $index = self::$formParentChildIndex ?? [];

        $childrenOfA = $index['parent_to_children'][$formA] ?? [];
        if (in_array($formB, $childrenOfA, true)) {
            return true;
        }

        $childrenOfB = $index['parent_to_children'][$formB] ?? [];
        if (in_array($formA, $childrenOfB, true)) {
            return true;
        }

        return false;
    }

    private static function bootFormParentChildIndex(): void
    {
        if (self::$formParentChildIndex !== null) {
            return;
        }

        $parentToChildren = [];
        try {
            $rows = RiceFormParentMap::query()
                ->where(function ($q) {
                    $q->where('status', 1)->orWhereNull('status');
                })
                ->get(['parent_form_id', 'child_form_ids']);

            foreach ($rows as $row) {
                $parentId = (int) $row->parent_form_id;
                if ($parentId <= 0) {
                    continue;
                }
                $children = [];
                foreach ((array) ($row->child_form_ids ?? []) as $childId) {
                    $childId = (int) $childId;
                    if ($childId > 0) {
                        $children[] = $childId;
                    }
                }
                if ($children !== []) {
                    $parentToChildren[$parentId] = array_values(array_unique($children));
                }
            }
        } catch (\Throwable $e) {
            $parentToChildren = [];
        }

        self::$formParentChildIndex = [
            'parent_to_children' => $parentToChildren,
        ];
    }

    private static function gradesMatch(int $tradeGradeId, int $interestGradeId, string $tradeWandLabel): bool
    {
        if ($tradeGradeId > 0 && $tradeGradeId === $interestGradeId) {
            return true;
        }

        if ($tradeGradeId <= 0 || $interestGradeId <= 0) {
            return false;
        }

        $interestLabel = self::cachedWandValueLabel($interestGradeId);

        return $tradeWandLabel !== '' && $interestLabel !== '' && $tradeWandLabel === $interestLabel;
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
