<?php

namespace App\Export;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VendorBulkImportSampleExport implements FromCollection, WithHeadings, WithStyles
{
    public static function columnHeadings(): array
    {
        return [
            'Name',
            'Email',
            'Mobile',
            'Company Name',
            'Contact Person',
            'Contact Mobile',
            'Address',
            'City',
            'State',
            'Product',
        ];
    }

    public function headings(): array
    {
        return self::columnHeadings();
    }

    public function collection()
    {
        return collect([
            [
                'Sample Vendor',
                'vendor@example.com',
                '9876543210',
                'Sample Company Pvt Ltd',
                'John Doe',
                '9876543210',
                '123 Sample Street',
                'Karnal',
                'Haryana',
                'Rice bags',
            ],
            [
                'Another Vendor',
                'another@example.com',
                '9876501234',
                'Another Traders',
                'Jane Smith',
                '9876501234',
                '45 Market Road',
                'Delhi',
                'Delhi',
                '',
            ],
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
