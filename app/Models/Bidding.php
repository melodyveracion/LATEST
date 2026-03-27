<?php

namespace App\Models;

use App\Models\Concerns\ExposesPrimaryKeyAsId;
use Illuminate\Database\Eloquent\Model;

class Bidding extends Model
{
    use ExposesPrimaryKeyAsId;

    protected $primaryKey = 'bidding_id';

    protected $fillable = [
        'consolidated_item_id',
        'supplier_name',
        'bid_amount',
        'status',
        'remarks',
        'bid_submitted_at',
        'awarded_at',
    ];

    protected $casts = [
        'bid_submitted_at' => 'datetime',
        'awarded_at' => 'datetime',
    ];
}
