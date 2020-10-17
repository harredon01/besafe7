<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;

class ArrayMultipleSheetImport implements WithMultipleSheets, SkipsUnknownSheets
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
            'quick' => new ArrayImport(),
        ];
    }
    public function onUnknownSheet($sheetName)
    {
        // E.g. you can log that a sheet was not found.
        //info("Sheet {$sheetName} was skipped");
    }
}
