<?php

namespace App\Export;

use App\PaddyPrice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaddyPricesApiExport implements FromQuery, WithMapping, WithHeadings, WithStyles, ShouldAutoSize
{
    /** @var list<int>|null null = no quality filter; [] = force empty result */
    private ?array $qualityIds = null;

    /**
     * @param  int|list<int>|null  $quality  Single quality id, list of ids, or null
     */
    public function __construct(
        private ?string $from = null,
        private ?string $to = null,
        int|array|null $quality = null
    ) {
        if (is_int($quality)) {
            $this->qualityIds = [$quality];
        } elseif (is_array($quality)) {
            $this->qualityIds = array_values(array_unique(array_map('intval', $quality)));
        } else {
            $this->qualityIds = null;
        }
    }

    public function query(): Builder
    {
        $candidates = PaddyPrice::query();
        $this->applyDateFilters($candidates);

        if ($this->qualityIds !== null) {
            if ($this->qualityIds === []) {
                $candidates->whereRaw('1 = 0');
            } else {
                $candidates->whereIn('quality_id', $this->qualityIds);
            }
        }

        // One row per calendar day × state × mandi × quality × crop year.
        $lastOfDayIds = (clone $candidates)
            ->selectRaw('MAX(id) as id')
            ->groupBy(
                DB::raw('DATE(created_at)'),
                'state',
                'mandi',
                'quality_id',
                'crop_year'
            );

        return PaddyPrice::query()
            ->with(['getMandi_rel', 'getState_rel', 'quality_rel'])
            ->whereIn('id', $lastOfDayIds)
            ->orderBy('created_at', 'DESC')
            ->orderBy('state')
            ->orderBy('mandi')
            ->orderBy('quality_id');
    }

    public function headings(): array
    {
        return [
            'price_date',
            'mandi',
            'state',
            'rice_type',
            'seed',
            'crop_year',
            'min_hand_cutting_price',
            'max_hand_cutting_price',
            'min_machine_cutting_price',
            'max_machine_cutting_price',
            'moisture',
            'total_arrivals',
            'change',
        ];
    }

    public function map($item): array
    {
        $quality = optional($item->quality_rel);

        return [
            $item->created_at ? $item->created_at->format('Y-m-d') : '',
            optional($item->getMandi_rel)->mandi ?? '',
            optional($item->getState_rel)->state ?? '',
            $quality->type_label ?? ($quality->type ?? ''),
            $quality->quality ?? '',
            $item->crop_year ?? '',
            $this->blankZeroOrNull($item->min_hand_cutting_price),
            $this->blankZeroOrNull($item->hand_cutting_price),
            $this->blankZeroOrNull($item->min_machine_cutting_price),
            $this->blankZeroOrNull($item->machine_cutting_price),
            $this->blankZeroOrNull($item->moisture),
            $this->blankZeroOrNull($item->total_arrivals),
            $item->change ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function applyDateFilters(Builder $query): void
    {
        $tz = config('app.timezone', 'Asia/Kolkata');
        if ($this->from) {
            $query->where('created_at', '>=', Carbon::parse($this->from, $tz)->startOfDay());
        }
        if ($this->to) {
            $query->where('created_at', '<=', Carbon::parse($this->to, $tz)->endOfDay());
        }
    }

    private function blankZeroOrNull(mixed $value): string|int|float
    {
        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '' || $trimmed === '----' || strcasecmp($trimmed, 'null') === 0) {
                return '';
            }
            if (! is_numeric($trimmed)) {
                return $value;
            }
            $value = $trimmed;
        }

        if (is_numeric($value) && (float) $value == 0.0) {
            return '';
        }

        return $value;
    }
}
