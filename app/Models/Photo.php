<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;

    protected $table = 'iz_photos';
    protected $primaryKey = 'id';  

    public static function stateSelect() {
        return "  SELECT  iz_photos.* FROM iz_photos ";
    }
    public static function stateWhere() {
        return "   WHERE iz_photos.id IS NOT NULL ";
    }
    public static function stateGroup() {
        return "  ";
    } 
    public static function insertData( $data ) { 
       
       \DB::table('iz_photos')->insert($data);
   }
   public static function updateData( $data , $key ) {
       
       \DB::table('iz_photos')->where('id' , $key  )->update($data);
   }
   public static function deleteData( $ids ) {        
        \DB::table('iz_photos')->whereIn('id' , explode(",",$ids)  )->delete();
    }
}
