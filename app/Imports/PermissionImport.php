<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Spatie\Permission\Models\Permission;

class PermissionImport implements ToModel
{
    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Permission([
            'name' => $row[0],
            'group_name' => $row[1],
        ]);
    }
}
