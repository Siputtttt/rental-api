<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'sx_menus';
    protected $primaryKey = 'menu_id';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'module',
        'url',
        'menu_name',
        'menu_type',
        'role_id',
        'deep',
        'ordering',
        'position',
        'menu_icons',
        'active',
        'access_data',
        'allow_guest',
        'menu_lang',
        'updated_at',
        'created_at'
    ];
}
