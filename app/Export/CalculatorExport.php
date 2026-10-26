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

    public function __construct(
        private ?string $from = null,
        private ?string $to = null,
        private ?int $packing = null
    ) {
    }

    public function query(): Builder
    {
        $candidates = USD_prices::query()
            ->whereIn('rice', QualityMaster::pluck('id'))
            ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->when($this->packing, fn ($q) => $q->where('usd_defaultMaster_id', $this->packing));

        // Only the last saved entry of each calendar day per (quality, packing,
        // packing type, rice type) group — earlier same-day saves are superseded
        // duplicates (the screen lists the freshest rows first; the export keeps
        // one final row per day so values are not duplicated).
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
            $item->ricemin,
            $item->ricemax,
            $packing->bag_size ?? '',
            $packing->bag_type ?? '',
            $quality->quality_type ?? '',
            $item->transportmin,
            $item->transportmax,
            $item->dollarrate,
            $item->fobmin,
            $item->fobmax,
            $item->created_at ? $item->created_at->format('Y-m-d H:i') : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
