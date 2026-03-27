<?php

namespace App\Models;

use App\Models\Concerns\ExposesPrimaryKeyAsId;
use Illuminate\Database\Eloquent\Model;

class FundSource extends Model
{
    use ExposesPrimaryKeyAsId;

    protected $primaryKey = 'fund_src_id';

    protected $fillable = [
        'name',
        'department_unit_id',
    ];
}
