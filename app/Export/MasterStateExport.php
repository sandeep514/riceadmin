<?php

namespace App\Export;

use App\Port;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MasterStateExport implements FromCollection, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return ['#', 'State / Port'];
    }

    public function collection()
    {
        $ports = Port::select('state')
            ->groupBy('state')
            ->where('state', '!=', '0')
            ->get();

        return $ports->values()->map(function ($item, $index) {
            return [
                $index + 1,
                $item->state,
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
