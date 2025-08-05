<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerekKendaraan extends Model
{
    use HasFactory;

    protected $table = 'm_merek_kendaraan';
    protected $primaryKey = 'id';  
 
}
