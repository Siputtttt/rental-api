<?php echo '<?php'; ?>

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class <?php echo e($moduleName); ?> extends Model
{
    use HasFactory;

    protected $table = '<?php echo e($moduleDB); ?>';
    protected $primaryKey = '<?php echo e($moduleDBKey); ?>';  

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
<?php /**PATH C:\laragon\www\SximoV7\Sximo-7-BE\resources\views/crud/Model.blade.php ENDPATH**/ ?>