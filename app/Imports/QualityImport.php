<?php

namespace App\Imports;

use App\Quality;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QualityImport implements ToModel, WithHeadingRow, ToCollection
{
    public $data;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Quality([
            'name' => $row['name'],
            'type' => $row['type'],
            'category' => $row['category']
        ]);
    }

    public function collection(Collection $collection)
    {
        $this->data = $collection;
    }
}
