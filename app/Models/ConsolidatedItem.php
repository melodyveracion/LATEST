<?php

namespace App\Models;

use App\Models\Concerns\ExposesPrimaryKeyAsId;
use Illuminate\Database\Eloquent\Model;

class ConsolidatedItem extends Model
{
    use ExposesPrimaryKeyAsId;

    protected $primaryKey = 'consol_item_id';

    protected $fillable = [
        'category_id',
        'item_name',
        'specifications',
        'unit',
        'total_quantity',
        'unit_price',
        'estimated_budget',
        'status',
        'created_by',
    ];
}
