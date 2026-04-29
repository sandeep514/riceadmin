<?php

namespace App\Export;

use App\RiceName;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MasterRiceNameExport implements FromCollection, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return ['#', 'Rice Quality', 'From Month', 'End Month', 'Type'];
    }

    public function collection()
    {
        return RiceName::get()->map(function ($item, $index) {
            return [
                $index + 1,
                $item->name,
                $item->from_month,
                $item->end_month,
                $item->type,
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
