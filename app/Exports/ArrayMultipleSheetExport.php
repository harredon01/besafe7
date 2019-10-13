<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ArrayMultipleSheetExport implements WithMultipleSheets
{
    use Exportable;

    protected $pages;
    
    public function __construct(array $pages)
    {
        $this->pages = $pages;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        foreach ($this->pages as $value) {
            $sheets[] = new ArraySheetExport($value['rows'],$value['name']);
        }
        return $sheets;
    }
}