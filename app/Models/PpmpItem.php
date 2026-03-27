<?php

namespace App\Models;

use App\Models\Concerns\ExposesPrimaryKeyAsId;
use Illuminate\Database\Eloquent\Model;

class PpmpItem extends Model
{
    use ExposesPrimaryKeyAsId;

    protected $primaryKey = 'ppmp_item_id';

    protected $fillable = [
        'ppmp_id',
        'category_id',
        'item_name',
        'specifications',
        'uacs_code',
        'description',
        'quantity',
        'unit',
        'unit_cost',
        'estimated_budget',
        'mode_of_procurement',
        'quantity_q1',
        'quantity_q2',
        'quantity_q3',
        'quantity_q4',
        'q1_total_cost',
        'q2_total_cost',
        'q3_total_cost',
        'q4_total_cost',
    ];

    public function ppmp()
    {
        return $this->belongsTo(Ppmp::class);
    }

    public function purchaseRequestItems()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }
}