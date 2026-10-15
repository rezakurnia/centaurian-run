<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ParticipantsImport implements ToArray, WithHeadingRow
{
    /**
     * Data hasil baca Excel (heading sebagai key).
     */
    public array $data = [];

    /**
     * Callback setiap baris dibaca.
     */
    public function array(array $rows): void
    {
        $this->data = $rows;
    }
}