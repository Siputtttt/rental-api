<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelKendaraan extends Model
{
    use HasFactory;

    protected $table = 't_model_kendaraan';
    protected $primaryKey = 'id';  
 
}
