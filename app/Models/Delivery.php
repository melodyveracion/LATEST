<?php

namespace App\Models;

use App\Models\Concerns\ExposesPrimaryKeyAsId;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use ExposesPrimaryKeyAsId;

    protected $primaryKey = 'delivery_id';

    protected $fillable = [
        'purchase_request_id',
        'consolidated_item_id',
        'supplier_name',
        'delivery_date',
        'received_by',
        'quantity_delivered',
        'status',
        'remarks',
    ];

    protected $casts = [
        'delivery_date' => 'date',
    ];
}
