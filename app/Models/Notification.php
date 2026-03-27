<?php

namespace App\Models;

use App\Models\Concerns\ExposesPrimaryKeyAsId;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use ExposesPrimaryKeyAsId;

    protected $primaryKey = 'notification_id';

    protected $fillable = [
        'user_id',
        'type',
        'reference_id',
        'status',
        'title',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
