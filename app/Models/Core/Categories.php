<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    protected $table = 'sx_categories';
    protected $primaryKey = 'id';

    public $timestamps = true; 

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'desc',
        'image',
        'active',
    ];
}
