<?php

namespace App\Export;

use App\RiceForm;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MasterRiceFormExport implements FromCollection, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return ['#', 'Rice Form Name', 'Type', 'Created At'];
    }

    public function collection()
    {
        return RiceForm::where('status', 1)->get()->map(function ($item, $index) {
            return [
                $index + 1,
                $item->form_name,
                $item->type,
                $item->created_at ? $item->created_at->format('d M Y') : '',
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
