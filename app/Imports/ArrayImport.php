<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;

class ArrayImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return [
            'fecha'  => $row['fecha'],
            'almuerzo' => $row['almuerzo'],
            'tipo'    => $row['tipo'],
            'plato'    => $row['plato'],
            'select'    => $row['select'],
            'descripcion'    => $row['descripcion'],
            'codigo'    => $row['codigo'],
        ];
    }
}
