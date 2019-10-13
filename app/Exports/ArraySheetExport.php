<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class ArraySheetExport implements FromArray, WithTitle
{
   protected $tuples;
   protected $title;

    public function __construct(array $tuples,$title)
    {
        $this->tuples = $tuples;
        $this->title = $title;
    }

    public function array(): array
    {
        return $this->tuples;
    }
    /**
     * @return string
     */
    public function title(): string
    {
        return substr($this->title, 0, 30);
    }
}
