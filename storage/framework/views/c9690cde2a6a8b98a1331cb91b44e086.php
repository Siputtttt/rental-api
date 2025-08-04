<?php echo '<?php'; ?>

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class <?php echo e($moduleName); ?> extends Sximo
{
    use HasFactory;

    public $table = '<?php echo e($moduleDB); ?>';
    public $primaryKey = '<?php echo e($moduleDBKey); ?>';  

    public function __construct() 
	{
		parent::__construct();
    }   
    public static function stateSelect() {
        return " <?php echo e($stateSelect); ?> ";
    }
    public static function stateWhere() {
        return "  <?php echo e($stateWhere); ?> ";
    }
    public static function stateGroup() {
        return " <?php echo e($stateGroup); ?> ";
    } 
    public static function insertData( $data ) { 
       
       \DB::table('<?php echo e($moduleDB); ?>')->insert($data);
   }
   public static function updateData( $data , $key ) {
       
       \DB::table('<?php echo e($moduleDB); ?>')->where('<?php echo e($moduleDBKey); ?>' , $key  )->update($data);
   }
   public static function deleteData( $ids ) {        
        \DB::table('<?php echo e($moduleDB); ?>')->whereIn('<?php echo e($moduleDBKey); ?>' , explode(",",$ids)  )->delete();
    }
}
<?php /**PATH D:\www\www\mangopik\sximo7\api\resources\views/crud/Model.blade.php ENDPATH**/ ?>