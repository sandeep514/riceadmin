<?php

namespace App\Export;

use App\QualityMaster;
use App\USD_prices;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CalculatorExport implements FromQuery, WithMapping, WithHeadings, WithStyles, ShouldAutoSize
{
    private int $rowNumber = 0;

    /** @var list<int>|null null = no packing filter; [] = force empty result */
    private ?array $packingIds = null;

    /**
     * @param  int|list<int>|null  $packing  Single master id, list of ids, or null (no packing filter)
     */
    public function __construct(
        private ?string $from = null,
        private ?string $to = null,
        int|array|null $packing = null,
        private ?string $type = null,
        private bool $ignoreZeroFob = true
    ) {
        if (is_int($packing)) {
            $this->packingIds = [$packing];
        } elseif (is_array($packing)) {
            $this->packingIds = array_values(array_unique(array_map('intval', $packing)));
        } else {
            $this->packingIds = null;
        }
    }

    public function query(): Builder
    {
        $qualityIds = QualityMaster::query()
            ->when($this->type, function ($q) {
                $q->whereRaw('LOWER(quality_type) = ?', [strtolower((string) $this->type)]);
            })
            ->pluck('id');

        $candidates = USD_prices::query()
            ->whereIn('rice', $qualityIds)
            ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('created_at', '<=', $this->to));

        if ($this->packingIds !== null) {
            if ($this->packingIds === []) {
                $candidates->whereRaw('1 = 0');
            } else {
                $candidates->whereIn('usd_defaultMaster_id', $this->packingIds);
            }
        }

        if ($this->ignoreZeroFob) {
            // Drop null / blank / zero FOB min rows entirely.
            $candidates->whereNotNull('fobmin')
                ->whereRaw('TRIM(fobmin) <> ""')
                ->whereRaw($this->positiveNumericSql('fobmin'));
        }

        // One row per calendar day × quality × packing (bag size/type via master id).
        // Earlier same-day saves for that combo are superseded duplicates.
        $lastOfDayIds = (clone $candidates)
            ->selectRaw('MAX(id) as id')
            ->groupBy(DB::raw('DATE(created_at)'), 'rice', 'usd_defaultMaster_id');

        return USD_prices::query()
            ->with(['getRiceQuality', 'getUSDDefaultMaster'])
            ->whereIn('id', $lastOfDayIds)
            ->orderBy('created_at', 'DESC')
            ->orderBy('rice')
            ->orderBy('usd_defaultMaster_id');
    }

    public function headings(): array
    {
        return [
            '#',
            'Quality',
            'Rice Min',
            'Rice Max',
            'Packing',
            'Packing Name',
            'Rice Type',
            'Transport Min',
            'Transport Max',
            'USD Rate',
            'FOB Min',
            'FOB Max',
            'Created At',
        ];
    }

    public function map($item): array
    {
        $this->rowNumber++;
        $quality = optional($item->getRiceQuality);
        $packing = optional($item->getUSDDefaultMaster);

        return [
            $this->rowNumber,
            trim(($quality->quality ?? '').' '.($quality->quality_name ?? '')),
            $this->blankZeroOrNull($item->ricemin),
            $this->blankZeroOrNull($item->ricemax),
            $packing->bag_size ?? '',
            $packing->bag_type ?? '',
            $quality->quality_type ?? '',
            $this->blankZeroOrNull($item->transportmin),
            $this->blankZeroOrNull($item->transportmax),
            $this->blankZeroOrNull($item->dollarrate),
            $this->blankZeroOrNull($item->fobmin),
            $this->blankZeroOrNull($item->fobmax),
            $item->created_at ? $item->created_at->format('Y-m-d H:i') : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * SQL fragment: column is a positive number (not null, not blank, not zero).
     */
    private function positiveNumericSql(string $column): string
    {
        return 'CAST(NULLIF(TRIM('.$column.'), "") AS DECIMAL(12,2)) > 0';
    }

    /**
     * Excel cells: hide null / empty / numeric-zero values.
     */
    private function blankZeroOrNull(mixed $value): string|int|float
    {
        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '' || ! is_numeric($trimmed)) {
                return $trimmed === '' ? '' : $value;
            }
            $value = $trimmed;
        }

        if (is_numeric($value) && (float) $value == 0.0) {
            return '';
        }

        return $value;
    }
}
