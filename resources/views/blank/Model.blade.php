<?php echo '<?php'; ?>

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {{ $moduleName }} extends Model
{
    use HasFactory;

    protected $table = '{{ $moduleDB }}';
    protected $primaryKey = '{{ $moduleDBKey }}';  
 
}
