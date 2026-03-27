<?php

namespace App\Models;

use App\Models\Concerns\ExposesPrimaryKeyAsId;
use Illuminate\Database\Eloquent\Model;

class DepartmentUnit extends Model
{
    use ExposesPrimaryKeyAsId;

    protected $primaryKey = 'department_unit_id';

    protected $fillable = [
        'name',
        'budget',
    ];
}
