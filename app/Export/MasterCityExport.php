<?php

namespace App\Export;

use App\LivePrice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MasterCityExport implements FromCollection, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return ['#', 'City / State', 'Order'];
    }

    public function collection()
    {
        $cities = LivePrice::select('id', 'state_order', 'state')
            ->groupBy('state')
            ->get();

        return $cities->map(function ($item, $index) {
            return [
                $index + 1,
                str_replace('_', ' ', $item->state),
                $item->state_order,
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
