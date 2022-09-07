<?php

namespace App\Exports;

use App\History;
use Maatwebsite\Excel\Concerns\FromCollection;

class HistoriesExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return History::all();
    }
}
