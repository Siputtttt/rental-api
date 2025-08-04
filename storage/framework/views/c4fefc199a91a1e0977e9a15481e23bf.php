<?php echo '<?php'; ?>

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class <?php echo e($moduleName); ?> extends Model
{
    use HasFactory;

    protected $table = '<?php echo e($moduleDB); ?>';
    protected $primaryKey = '<?php echo e($moduleDBKey); ?>';  
 
}
<?php /**PATH D:\www\www\mangopik\sximo7\api\resources\views/blank/Model.blade.php ENDPATH**/ ?>