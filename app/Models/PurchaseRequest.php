<?php

namespace App\Models;

use App\Models\Concerns\ExposesPrimaryKeyAsId;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    use ExposesPrimaryKeyAsId;

    protected $primaryKey = 'pr_id';

    protected $fillable = [
        'user_id',
        'department_unit_id',
        'fund_source_id',
        'ppmp_id',
        'purpose',
        'status',
        'submitted_at',
        'confirmed_at',
        'review_remarks',
        'reviewed_by',
        'reviewed_at',
        'award_notice_path',
        'failure_notice_path',
        'bac_notice_type',
        'bac_notice_path',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ppmp()
    {
        return $this->belongsTo(Ppmp::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }
}
