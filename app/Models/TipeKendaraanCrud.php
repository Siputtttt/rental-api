<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipeKendaraanCrud extends Sximo
{
    use HasFactory;

    public $table = 'm_tipe_kendaraan';
    public $primaryKey = 'id';  

    public function __construct() 
	{
		parent::__construct();
    }   
    public static function stateSelect() {
        return "  SELECT  m_tipe_kendaraan.* FROM m_tipe_kendaraan ";
    }
    public static function stateWhere() {
        return "   WHERE m_tipe_kendaraan.id IS NOT NULL ";
    }
    public static function stateGroup() {
        return "  ";
    } 
    public static function insertData( $data ) { 
       
       \DB::table('m_tipe_kendaraan')->insert($data);
   }
   public static function updateData( $data , $key ) {
       
       \DB::table('m_tipe_kendaraan')->where('id' , $key  )->update($data);
   }
   public static function deleteData( $ids ) {        
        \DB::table('m_tipe_kendaraan')->whereIn('id' , explode(",",$ids)  )->delete();
    }
}
