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
            'Min Hand Cutting Price',
            'Max Hand Cutting Price',
            'Min Machine Cutting Price',
            'Max Machine Cutting Price',
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
                $this->blankZeroOrNull($item->min_hand_cutting_price),
                $this->blankZeroOrNull($item->hand_cutting_price),
                $this->blankZeroOrNull($item->min_machine_cutting_price),
                $this->blankZeroOrNull($item->machine_cutting_price),
                $this->blankZeroOrNull($item->moisture),
                $this->blankZeroOrNull($item->total_arrivals),
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

    /**
     * Excel cells: hide null / empty / "----" / numeric-zero values.
     */
    private function blankZeroOrNull(mixed $value): string|int|float
    {
        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '' || $trimmed === '----' || strcasecmp($trimmed, 'null') === 0) {
                return '';
            }
            if (! is_numeric($trimmed)) {
                return $value;
            }
            $value = $trimmed;
        }

        if (is_numeric($value) && (float) $value == 0.0) {
            return '';
        }

        return $value;
    }
}
