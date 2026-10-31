<?php

namespace App\Export;

use App\PaddyPrice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PaddyPricesApiExport implements FromQuery, WithMapping, WithEvents, WithCustomStartCell, ShouldAutoSize
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

    public function startCell(): string
    {
        return 'A3';
    }

    public function map($item): array
    {
        $quality = optional($item->quality_rel);

        return [
            $item->created_at ? $item->created_at->format('Y-m-d') : '',
            optional($item->getState_rel)->state ?? '',
            optional($item->getMandi_rel)->mandi ?? '',
            $quality->quality ?? '',
            $item->crop_year ?? '',
            $this->blankZeroOrNull($item->min_machine_cutting_price),
            $this->blankZeroOrNull($item->machine_cutting_price),
            $this->blankZeroOrNull($item->min_hand_cutting_price),
            $this->blankZeroOrNull($item->hand_cutting_price),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->setCellValue('A1', 'Date');
                $sheet->setCellValue('B1', 'State');
                $sheet->setCellValue('C1', 'Mandi');
                $sheet->setCellValue('D1', 'Quality');
                $sheet->setCellValue('E1', 'Crop Year');
                $sheet->setCellValue('F1', 'Machine Cutting (INR)');
                $sheet->setCellValue('H1', 'Hand Cutting (INR)');
                $sheet->setCellValue('F2', 'Min');
                $sheet->setCellValue('G2', 'Max');
                $sheet->setCellValue('H2', 'Min');
                $sheet->setCellValue('I2', 'Max');

                $sheet->mergeCells('A1:A2');
                $sheet->mergeCells('B1:B2');
                $sheet->mergeCells('C1:C2');
                $sheet->mergeCells('D1:D2');
                $sheet->mergeCells('E1:E2');
                $sheet->mergeCells('F1:G1');
                $sheet->mergeCells('H1:I1');

                $sheet->getStyle('A1:I2')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'B8CCE4'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                $sheet->getRowDimension(1)->setRowHeight(22);
                $sheet->getRowDimension(2)->setRowHeight(20);
            },
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
