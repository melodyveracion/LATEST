<?php

namespace App\Models;

use App\Models\Concerns\ExposesPrimaryKeyAsId;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use ExposesPrimaryKeyAsId;

    protected $primaryKey = 'inventory_id';

    protected $fillable = [
        'category_id',
        'item_name',
        'unit',
        'quantity_on_hand',
        'last_delivery_id',
    ];
}
