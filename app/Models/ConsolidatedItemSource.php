<?php

namespace App\Models;

use App\Models\Concerns\ExposesPrimaryKeyAsId;
use Illuminate\Database\Eloquent\Model;

class ConsolidatedItemSource extends Model
{
    use ExposesPrimaryKeyAsId;

    protected $primaryKey = 'consol_item_src_id';

    protected $fillable = [
        'consolidated_item_id',
        'purchase_request_id',
        'purchase_request_item_id',
        'source_quantity',
        'source_amount',
    ];
}
