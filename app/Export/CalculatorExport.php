<?php

namespace App\Export;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CalculatorExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function __construct(private Collection $rows)
    {
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

    public function collection()
    {
        return $this->rows->values()->map(function ($item, $index) {
            $quality = optional($item->getRiceQuality);
            $packing = optional($item->getUSDDefaultMaster);

            return [
                $index + 1,
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
        });
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
