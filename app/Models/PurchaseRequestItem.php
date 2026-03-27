<?php

namespace App\Models;

use App\Models\Concerns\ExposesPrimaryKeyAsId;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    use ExposesPrimaryKeyAsId;

    protected $primaryKey = 'pr_item_id';

    protected $fillable = [
        'purchase_request_id',
        'ppmp_item_id',
        'category_id',
        'item_name',
        'specifications',
        'unit',
        'quantity',
        'unit_price',
        'q1_total_cost',
        'q2_total_cost',
        'q3_total_cost',
        'q4_total_cost',
        'mode_of_procurement',
        'estimated_budget',
        'jan',
        'feb',
        'mar',
        'apr',
        'may',
        'jun',
        'jul',
        'aug',
        'sep',
        'oct',
        'nov',
        'dec',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function ppmpItem()
    {
        return $this->belongsTo(PpmpItem::class);
    }
}
