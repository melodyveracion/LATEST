<?php

namespace App\Models;

use App\Models\Concerns\ExposesPrimaryKeyAsId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PresetItem extends Model
{
    use SoftDeletes;
    use ExposesPrimaryKeyAsId;

    protected $table = 'preset_items';
    protected $primaryKey = 'project_id';

    protected $fillable = [
        'category_id',
        'part_label',
        'item_name',
        'unit',
        'price',
    ];
}
