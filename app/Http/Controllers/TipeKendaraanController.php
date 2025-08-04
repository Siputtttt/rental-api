<?php

namespace App\Http\Controllers;
use App\Models\TipeKendaraan;
use App\Services\Core\ConfigService;
use App\Exports\Download;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class TipeKendaraanController extends Controller
{
public $configService;
public $config;
public $access ;
public $search ;

    public function __construct(  ConfigService $configService ,  Request $request)
    { 
        $this->configService = $configService ;
        /* Prepare all configuration cruds */
        $this->config =  $configService->prepareSystem('TipeKendaraan' , new TipeKendaraan());
        /* Prepare all access to module */
        $this->access = $this->configService->setupAccess($request->user()->group_id);
        /* Prepare all search */
        $this->search = $this->configService->setupSearch( $request); 
    }

    public function index( Request $request)
    {        
        if( !$this->access->is_view )
            return $this->configService->restricted();

        /* build filter search. If you need custom serach you can append string conditional to onSearch variable */
        $onSearch =  $this->search; 
        /* Grab Data from database after all configuration */
        $rows = $this->configService->setupData( $onSearch ) ;
        /* Send all module configuration */
        return response()->json([ 
            'columns'   => $this->config->columns ,
            'forms'     => $this->config->forms ,
            'rows'      => $rows ,
            'field'     => $this->config->field ,
            'items'     => $this->config->items ,
            'options'   => $this->config->option ,
            'setting'   => $this->config->setting,
            'access'    => $this->access , 
            'status'    => 1 
        ],200);
    }
    
    public function create(  )
    {        
        if( !$this->access->is_add )
            return $this->configService->restricted();

        return response()->json([
            'status'=> 1 ,   'data' => $this->config->items , 'message' => 'successfull'  
        ],200); 
    }
    public function store(Request $request)
    { 
        if( !$this->access->is_add ||  !$this->access->is_edit )
            return $this->configService->restricted();

              
        $check = $this->configService->validate();
        if(count($check) > 0 ) {    
            $validator = (object) $request->validate( $check ); 
        }
        // make sure only registered fields can be saved into database 
        $posts = $this->configService->validatePosts( $request );  
        if( $request->id != '') {
           
            TipeKendaraan::updateData( $posts , $request->id  );
            $action = 'update';        
            $message = " Data has been updated ";
        } else { 
            TipeKendaraan::insertData( $posts );
            $action = 'insert';        
            $message = " Data has been inserted ";
        }
        // Insert Log History
        TipeKendaraan::auditLogs( $request->user()->id , 'TipeKendaraan', $action, $posts);
        return response()->json([
            'status'      => 1 , 
            'posts'         => $posts,
            'message'     => $message
        ],200);   
    }

    public function show( Request $request , $task)
    {
        switch( $task ) {
            default:
                $row = $this->configService->setupEdit( $request->id );
                 return response()->json(['status'=> 1 ,   'data' => $row  ],200);
                break;
            
            case 'download':
                $rows = $this->configService->setupData(  $this->search   ) ;

                Excel::store( new Download([
                    'rows' => $rows ,
                    'headers' => $this->configService->download 
                ]),'downloads.xls' ,'public'  );

                $path  =  storage_path() . '/app/public/downloads.xls' ; 
                return response()->download($path); 
                break ; 
        }
    } 

    public function destroy($ids)
    {
        if( !$this->access->is_delete )
            return $this->configService->restricted();

        try {
            TipeKendaraan::deleteData( $ids );
            return ['status'=> 1 ,   'message' => 'Data has been deleted' ,'data' => $ids   ];
        } catch(Exception $e) {

        }
    }
}
