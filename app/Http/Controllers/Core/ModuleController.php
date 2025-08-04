<?php

namespace App\Http\Controllers\Core;
use App\Services\Core\ModuleService;
use App\Services\Core\ConfigService;
use App\Models\Core\Modules;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModuleController extends Controller
{
    //
    public $moduleService;
    public $configService;
    public function __construct(  ModuleService $moduleService  , ConfigService $configService)
    {
        
        $this->moduleService = $moduleService;
        $this->configService = $configService;
    }

    public function index( Request $request) {
        $rows = Modules::index();
        $tableDb = Modules::showTable(); 

        return [
            'status'        => 1 , 
            'table'         => $tableDb ,
            'rows'          => $rows
        ];
    }
    public function show( Request $request , $task ) {
        if( $task =='rebuild') {
            
            $row = Modules::detail( $request->id );
            $post = (object)  [
                'module_name'   => $row->module_name,
                'module_title'  => $row->module_title,
                'module_note'   => $row->module_note,
                'module_db'     => $row->module_db,
                'module_type'   => $row->module_type ,
                'module_id'     => $request->id
            ];            
            if($this->moduleService->prepareModule( $post )) {
                $return = $this->moduleService->buildModule( true );
            } 
            return [
                'status'   => 1 , 
                'rows'     =>  $post
            ];
        }
        if( $task =='remove') {
            $moduleName = $request->id ;
            $this->moduleService->removeModule( $moduleName );
            return [
                'status'   => 1 , 
                'rows'     => $moduleName
            ];
        }

        if( $task =='permission') {
        
            return [
                'status'   => 1 , 
                'rows'     => []
            ];
        }

    }

    public function store( Request $request  ) {
        switch($request->task)
        {
            default:
                break;

            case 'save_create':    
                return $this->save_create( $request );
                break; 

            case 'save_config':    
                return $this->save_config( $request );
                break;    
                
            case 'save_statement':    
                return $this->save_statement( $request );
                break;     
        }
    }
    public function save_create( $request ) {

        $post = (object) $request->validate([
            'module_name' => 'required|unique:sx_modules|alpha|min:2|',
            'module_title' => 'required',
            'module_note' => 'required',
            'module_db' => 'required', 
            'module_type' => 'nullable|max:255' 
        ]);
        $post->module_id = '';
        $return = [];
        if($this->moduleService->prepareModule( $post )) {
            $return = $this->moduleService->buildModule( false );
        }
        return [
            'status'      => 1 , 
            'message'     => 'New Module Generated !'
        ];
    }

    public function save_statement( $request ) {
        $post = json_decode($request->rows );   
        
        try {   
            DB::select(  $post->sql_select .' '.$post->sql_where.' '. $post->sql_group ); 
            
        } catch( \Illuminate\Database\QueryException   $e){ 
            $error ='Error : '. $post->sql_select .' '.$post->sql_where.' '. $post->sql_group  ; 
            return response()->json([ 
                'status'        => false , 
                'message'       => 'Error Statement :  ' . $error
            ]);
        }

        $statement = [
            'sql_select'    => $post->sql_select ,
            'sql_where'     => $post->sql_where ,
            'sql_group'     => $post->sql_group 
        ]; 
        $res = $this->moduleService->rebuildColumns( $statement , $post->tables , $post->forms , $post->module_db );
        $rebase = [
            'sql_select'    => $post->sql_select ,
            'sql_where'     => $post->sql_where ,
            'sql_group'     => $post->sql_group ,
            'table_db'      => $post->table_db ,
            'primary_key'   => $post->primary_key ,
            'settings'      => $post->settings ,
            'forms'         => $res['forms'] ,
            'grid'          => $res['tables'] ,
        ];
       \DB::table('sx_modules')->where('module_id', $post->module_id)->update(['module_config' => json_encode($rebase)]);  
        return response()->json([
            'data'          => $rebase ,
            'status'        => 1 , 
            'message'       => 'Statement Success !'
        ]);
    }

    public function save_config( $request ) {
        $post = json_decode($request->rows );         
        $rebase = [
            'sql_select'    => $post->sql_select ,
            'sql_where'     => $post->sql_where ,
            'sql_group'     => $post->sql_group ,
            'table_db'      => $post->table_db ,
            'primary_key'   => $post->primary_key ,
            'settings'      => $post->settings ,
            'forms'         => $post->forms ,
            'grid'         => $post->tables 
        ];
        $update = [
            'module_name'   => $post->module_name ,
            'module_title'   => $post->module_title ,
            'module_note'   => $post->module_note ,
            //'module_desc'   => $post->module_desc ,
            'module_type'   => $post->module_type ,
            'module_author'   => $request->user()->username ,
            'module_db'   => $post->module_db ,
            'module_db_key'   => $post->module_db ,
            'module_config'   =>json_encode($rebase)

        ];
        \DB::table('sx_modules')->where('module_id', $post->module_id)->update($update);  
        \DB::table('sx_groups_access')->where('module_id', $post->module_id)->delete();
        foreach($post->access as $ac) {
            \DB::table('sx_groups_access')->insert([
                'module_id' => $post->module_id , 
                'group_id' => $ac->id , 
                'access_data' => json_encode($ac->access)
            ]);
        }
        //\DB::table('sx_group_access')->where('module_id', $post->module_id)->delete();

        return [
            'status'      => 1 , 
            'message'     => 'All Change(s) Has been save , successful',
            'groups'        => $post->access ,
            'data'          => $rebase
        ];
        
    } 
}
