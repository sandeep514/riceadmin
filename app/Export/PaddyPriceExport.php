<?php

namespace App\Export;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaddyPriceExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function __construct(private Collection $rows)
    {
    }

    public function headings(): array
    {
        return [
            '#',
            'Mandi',
            'State',
            'Quality',
            'Crop Year',
            'Hand Cutting Price',
            'Machine Cutting Price',
            'Moisture',
            'Total Arrivals',
            'Change',
            'Date',
            'Status',
        ];
    }

    public function collection()
    {
        return $this->rows->values()->map(function ($item, $index) {
            return [
                $index + 1,
                optional($item->getMandi_rel)->mandi ?? '',
                optional($item->getState_rel)->state ?? '',
                optional($item->quality_rel)->quality
                    ? ($item->quality_rel->type_label.' - '.$item->quality_rel->quality)
                    : '',
                $item->crop_year,
                $item->hand_cutting_price,
                $item->machine_cutting_price,
                $item->moisture,
                $item->total_arrivals,
                $item->change,
                $item->created_at ? $item->created_at->format('Y-m-d') : '',
                ((int) $item->status === 1) ? 'Active' : 'Inactive',
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
