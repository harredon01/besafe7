<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ArrayMultipleSheetImport implements WithMultipleSheets
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function sheets(): array
    {
        return [
            'merchants' => new ArrayImport(),
            'reports' => new ArrayImport(),
            'products' => new ArrayImport(),
            'variants' => new ArrayImport(),
            'availabilities' => new ArrayImport(),
            'categories' => new ArrayImport(),
            'ratings' => new ArrayImport(),
            'polygons' => new ArrayImport(),
        ];
    }
}
