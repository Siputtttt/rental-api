<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MerekKendaraan extends Model
{
    use HasFactory;

    protected $table = 'm_merek_kendaraan';
    protected $primaryKey = 'id';  
 
    public static function getDataMerek(){
        $data = DB::table('m_merek_kendaraan')->get();

        foreach ($data as $key => $value) {
            $data[$key]->gambar = asset('storage/uploads/merek/' . $value->gambar) ;
        }

        return $data;
    }
}
