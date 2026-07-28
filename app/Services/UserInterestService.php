<?php

namespace App\Services;

use App\RiceForm;
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
    private static array $liveFormNameCache = [];

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
        if ($userId <= 0) {
            return [];
        }

        // Prefer status=1; if none (legacy rows), still return rows for this user.
        $rows = UserInterestedMap::query()
            ->where('user_id', $userId)
            ->where(function ($q) {
                $q->where('status', 1)->orWhere('status', true)->orWhereNull('status');
            })
            ->get(['rice_name_id', 'form_id', 'grade']);

        if ($rows->isEmpty()) {
            $rows = UserInterestedMap::query()
                ->where('user_id', $userId)
                ->get(['rice_name_id', 'form_id', 'grade']);
        }

        return $rows
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
            ->filter(fn ($row) => $row['rice_name_id'] > 0)
            ->values()
            ->all();
    }

    /**
     * Match trade against user Preferred products (rice name + rice form).
     *
     * Preferred only when BOTH match (e.g. PR-11/14 + Raw — not every PR-11/14).
     *
     * Scores (higher first):
     * 3 = rice name + form + grade
     * 2 = rice name + form  ← Preferred
     * 0 = no match (name alone is NOT preferred)
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
        $tradeLiveFormId = (int) ($trade->qualityFormLinkWithLivePrice ?? 0);
        $tradeGrade = (int) ($trade->grade ?? 0);

        $tradeRiceLabel = self::resolveTradeRiceLabel($trade, $quality);
        $tradeFormLabel = self::resolveTradeFormLabel($trade, $tradeFormId, $tradeLiveFormId);
        $tradeWandLabel = $tradeGrade > 0 ? self::cachedWandValueLabel($tradeGrade) : '';
        $best = 0;

        foreach ($tuples as $tuple) {
            $interestNameId = (int) $tuple['rice_name_id'];
            $interestFormId = (int) ($tuple['form_id'] ?? 0);
            $interestGrade = $tuple['grade'] !== null ? (int) $tuple['grade'] : null;

            if ($interestNameId <= 0 || $interestFormId <= 0) {
                continue;
            }

            // Both rice name AND form are required for Preferred.
            if (! self::riceNamesMatch($quality, $interestNameId, $tradeRiceLabel)) {
                continue;
            }

            if (! self::formsMatch($tradeFormId, $interestFormId, $tradeFormLabel, $tradeLiveFormId)) {
                continue;
            }

            $best = max($best, 2);

            if ($interestGrade !== null && $interestGrade > 0 && self::gradesMatch($tradeGrade, $interestGrade, $tradeWandLabel)) {
                $best = max($best, 3);
            }
        }

        return $best;
    }

    /**
     * Resolve trade rice-name label from master table or eager relation.
     */
    private static function resolveTradeRiceLabel(object $trade, int $quality): string
    {
        $label = self::cachedRiceNameLabel($quality);
        if ($label !== '') {
            return $label;
        }

        return self::normalizeLabel(
            (string) (data_get($trade, 'RiceNameData.name')
                ?? data_get($trade, 'rice_name_data.name')
                ?? data_get($trade, 'RiceQualityMaster.quality_name')
                ?? data_get($trade, 'RiceQualityMaster.quality')
                ?? '')
        );
    }

    /**
     * Resolve trade form label from milestone3, live-price form, or relations.
     */
    private static function resolveTradeFormLabel(object $trade, int $tradeFormId, int $tradeLiveFormId): string
    {
        if ($tradeFormId > 0) {
            $label = self::cachedFormNameLabel($tradeFormId);
            if ($label !== '') {
                return $label;
            }
            // qualityForm may point at legacy rice_forms id on older rows.
            $label = self::cachedLiveFormNameLabel($tradeFormId);
            if ($label !== '') {
                return $label;
            }
        }

        $rel = self::normalizeLabel(
            (string) (data_get($trade, 'RiceFormMilestone3.name')
                ?? data_get($trade, 'rice_form_milestone3.name')
                ?? '')
        );
        if ($rel !== '') {
            return $rel;
        }

        if ($tradeLiveFormId > 0) {
            $label = self::cachedLiveFormNameLabel($tradeLiveFormId);
            if ($label !== '') {
                return $label;
            }
        }

        return self::normalizeLabel(
            (string) (data_get($trade, 'RiceFormData.form_name')
                ?? data_get($trade, 'rice_form_data.form_name')
                ?? '')
        );
    }

    /**
     * Normalize for equality: "PR-11/14", "PR 11/14", "PR-11-14" → "pr1114"; "Raw" → "raw".
     */
    private static function normalizeLabel(?string $value): string
    {
        $value = strtolower(trim((string) $value));
        // Keep only letters/digits so punctuation/spacing variants still match.
        $value = preg_replace('/[^a-z0-9]+/', '', $value) ?? '';

        return $value;
    }

    private static function labelsEqual(string $a, string $b): bool
    {
        return $a !== '' && $b !== '' && $a === $b;
    }

    /**
     * Form labels: exact match, or shorter label (min 3 chars) is prefix of longer
     * so "raw" matches "rawrice", "steam" matches "steamsuper".
     */
    private static function formLabelsEqual(string $a, string $b): bool
    {
        if (self::labelsEqual($a, $b)) {
            return true;
        }
        if ($a === '' || $b === '') {
            return false;
        }
        $short = strlen($a) <= strlen($b) ? $a : $b;
        $long = strlen($a) <= strlen($b) ? $b : $a;
        if (strlen($short) < 3) {
            return false;
        }

        return str_starts_with($long, $short);
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

    private static function cachedLiveFormNameLabel(int $id): string
    {
        if ($id <= 0) {
            return '';
        }
        if (! array_key_exists($id, self::$liveFormNameCache)) {
            try {
                self::$liveFormNameCache[$id] = self::normalizeLabel(
                    (string) (RiceForm::query()->where('id', $id)->value('form_name') ?? '')
                );
            } catch (\Throwable $e) {
                self::$liveFormNameCache[$id] = '';
            }
        }

        return self::$liveFormNameCache[$id];
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
        if ($tradeQualityId > 0 && $tradeQualityId === $interestNameId) {
            return true;
        }

        $interestLabel = self::cachedRiceNameLabel($interestNameId);

        return self::labelsEqual($tradeRiceLabel, $interestLabel);
    }

    private static function formsMatch(int $tradeFormId, int $interestFormId, string $tradeFormLabel, int $tradeLiveFormId = 0): bool
    {
        if ($interestFormId <= 0) {
            return false;
        }

        if ($tradeFormId > 0 && $tradeFormId === $interestFormId) {
            return true;
        }

        // Expanded parent/child (and sibling) form ids for Preferred form map.
        if ($tradeFormId > 0) {
            $expanded = self::expandedFormIds($interestFormId);
            if (isset($expanded[$tradeFormId])) {
                return true;
            }
        }

        $interestLabel = self::cachedFormNameLabel($interestFormId);
        if (self::formLabelsEqual($tradeFormLabel, $interestLabel)) {
            return true;
        }

        // Live-price form name (rice_forms) may still equal Preferred form name.
        if ($tradeLiveFormId > 0) {
            $liveLabel = self::cachedLiveFormNameLabel($tradeLiveFormId);
            if (self::formLabelsEqual($liveLabel, $interestLabel)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Interest form id + its parents + children (for parent/child form maps).
     *
     * @return array<int, true>
     */
    private static function expandedFormIds(int $formId): array
    {
        if ($formId <= 0) {
            return [];
        }

        self::bootFormParentChildIndex();
        $index = self::$formParentChildIndex ?? [];
        $parentToChildren = $index['parent_to_children'] ?? [];
        $childToParents = $index['child_to_parents'] ?? [];

        $set = [$formId => true];

        foreach ($parentToChildren[$formId] ?? [] as $childId) {
            $set[(int) $childId] = true;
        }
        foreach ($childToParents[$formId] ?? [] as $parentId) {
            $parentId = (int) $parentId;
            $set[$parentId] = true;
            foreach ($parentToChildren[$parentId] ?? [] as $siblingId) {
                $set[(int) $siblingId] = true;
            }
        }

        return $set;
    }

    private static function formsLinkedByParentMap(int $formA, int $formB): bool
    {
        $expanded = self::expandedFormIds($formA);

        return isset($expanded[$formB]);
    }

    private static function bootFormParentChildIndex(): void
    {
        if (self::$formParentChildIndex !== null) {
            return;
        }

        $parentToChildren = [];
        $childToParents = [];
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
                        $childToParents[$childId] = $childToParents[$childId] ?? [];
                        $childToParents[$childId][] = $parentId;
                    }
                }
                if ($children !== []) {
                    $parentToChildren[$parentId] = array_values(array_unique($children));
                }
            }
        } catch (\Throwable $e) {
            $parentToChildren = [];
            $childToParents = [];
        }

        self::$formParentChildIndex = [
            'parent_to_children' => $parentToChildren,
            'child_to_parents' => $childToParents,
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
