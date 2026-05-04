<?php

namespace App\Export;

use App\RiceFormMilestone3;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MasterRiceFormMilestone3Export implements FromCollection, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return ['#', 'Name', 'Order', 'Status'];
    }

    public function collection()
    {
        return RiceFormMilestone3::orderBy('order', 'ASC')->get()->map(function ($item, $index) {
            return [
                $index + 1,
                $item->name,
                $item->order,
                $item->status == 1 ? 'Active' : 'Inactive',
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
