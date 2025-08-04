<?php echo '<?php'; ?>

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {{ $moduleName }} extends Sximo
{
    use HasFactory;

    public $table = '{{ $moduleDB }}';
    public $primaryKey = '{{ $moduleDBKey }}';  

    public function __construct() 
	{
		parent::__construct();
    }   
    public static function stateSelect() {
        return " {{ $stateSelect }} ";
    }
    public static function stateWhere() {
        return "  {{ $stateWhere }} ";
    }
    public static function stateGroup() {
        return " {{ $stateGroup }} ";
    } 
    public static function insertData( $data ) { 
       
       \DB::table('{{ $moduleDB }}')->insert($data);
   }
   public static function updateData( $data , $key ) {
       
       \DB::table('{{ $moduleDB }}')->where('{{ $moduleDBKey }}' , $key  )->update($data);
   }
   public static function deleteData( $ids ) {        
        \DB::table('{{ $moduleDB }}')->whereIn('{{ $moduleDBKey }}' , explode(",",$ids)  )->delete();
    }
}
