<?php

namespace App\Export;

use App\RiceBrandForm;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MasterRiceBrandFormExport implements FromCollection, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return ['#', 'Rice Brand Form Name', 'Type', 'Status'];
    }

    public function collection()
    {
        return RiceBrandForm::select('id', 'form_name', 'type', 'status')->get()
            ->map(function ($item, $index) {
                return [
                    $index + 1,
                    $item->form_name,
                    $item->type,
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
