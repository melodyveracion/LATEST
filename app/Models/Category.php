<?php

namespace App\Models;

use App\Models\Concerns\ExposesPrimaryKeyAsId;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use ExposesPrimaryKeyAsId;

    protected $primaryKey = 'category_id';

    protected $fillable = [
        'name',
    ];
}
