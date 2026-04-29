<?php

namespace App\Export;

use App\WandModel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WandExport implements FromCollection, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return ['#', 'Rice Name', 'Wand Type', 'Value', 'Order', 'Status'];
    }

    public function collection()
    {
        return WandModel::with(['getWandType'])
            ->join('rice_names', 'wand.RiceNameId', '=', 'rice_names.id')
            ->select('wand.*', 'rice_names.name as rice_name')
            ->orderBy('rice_names.name')
            ->orderBy('wand.order')
            ->get()
            ->map(function ($item, $index) {
                return [
                    $index + 1,
                    $item->rice_name,
                    $item->getWandType ? $item->getWandType->type : '',
                    $item->value,
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
