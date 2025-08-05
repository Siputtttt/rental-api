<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipeKendaraan extends Model
{
    use HasFactory;

    protected $table = 'm_tipe_kendaraan';
    protected $primaryKey = 'id';  
 
}
