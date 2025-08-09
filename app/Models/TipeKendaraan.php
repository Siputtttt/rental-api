<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TipeKendaraan extends Model
{
    use HasFactory;

    protected $table = 'm_tipe_kendaraan';
    protected $primaryKey = 'id';

    protected $fillable = [
        'tipe',
    ];
    public $timestamps = true;

    public static function deleteData( $ids ) {        
        DB::table('m_tipe_kendaraan')->whereIn('id' , explode(",",$ids)  )->delete();
    }
}
