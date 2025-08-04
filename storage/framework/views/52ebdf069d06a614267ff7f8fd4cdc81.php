<?php echo '<?php'; ?>

 
namespace App\Http\Controllers;
use App\Models\<?php echo e($modelClassName); ?>;
use App\Services\Core\ConfigService; 
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator; 

class <?php echo e($controllerClassName); ?> extends Controller
{
    public $configService; 
    public $access ; 
    public $config;

    public function __construct(  ConfigService $configService ,  Request $request)
    {  
        
        $this->configService = $configService ;
        /* Prepare all configuration cruds */
        $this->config =  $configService->prepareSystem('<?php echo e($moduleName); ?>' , new <?php echo e($modelClassName); ?>());
        /* Prepare all access to module */
        $this->access = $this->configService->setupAccess($request->user()->group_id); 
        
    }

    public function index( Request $request)
    {        
        if( !$this->access->is_view )
            return $this->configService->restricted();

        return response()->json([ ],200);
    }
    
    public function create(  )
    {        
        if( !$this->access->is_add )
            return $this->configService->restricted();

        return response()->json([],200); 
    }
    public function store(Request $request)
    { 
        if( !$this->access->is_add ||  !$this->access->is_edit )
            return $this->configService->restricted(); 

        return response()->json([ ],200);   
    }

    public function show( Request $request , $task)
    {
        
    } 

    public function destroy($ids)
    {
        if( !$this->access->is_delete )
            return $this->configService->restricted(); 
    }
}
<?php /**PATH /Users/haimac/Documents/jobs/side/rental-api/resources/views/blank/Controller.blade.php ENDPATH**/ ?>