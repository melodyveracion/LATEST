<?php

namespace App\Models;

use App\Models\Concerns\ExposesPrimaryKeyAsId;
use Illuminate\Database\Eloquent\Model;

class Ppmp extends Model
{
    use ExposesPrimaryKeyAsId;

    protected $primaryKey = 'ppmp_id';

    protected $fillable = [
        'user_id',
        'department_unit_id',
        'fund_source_id',
        'fiscal_year',
        'ppmp_no',
        'status',
        'submitted_at',
        'review_remarks',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PpmpItem::class);
    }

    public function purchaseRequests()
    {
        return $this->hasMany(PurchaseRequest::class);
    }
}
