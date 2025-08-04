<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;

    protected $table = 'iz_album';
    protected $primaryKey = 'id';  

    public static function stateSelect() {
        return "  SELECT  iz_album.* FROM iz_album ";
    }
    public static function stateWhere() {
        return "   WHERE iz_album.id IS NOT NULL ";
    }
    public static function stateGroup() {
        return "  ";
    } 
    public static function insertData( $data ) { 
       
       \DB::table('iz_album')->insert($data);
   }
   public static function updateData( $data , $key ) {
       
       \DB::table('iz_album')->where('id' , $key  )->update($data);
   }
   public static function deleteData( $ids ) {        
        \DB::table('iz_album')->whereIn('id' , explode(",",$ids)  )->delete();
    }
}
