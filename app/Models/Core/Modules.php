<?php
namespace App\Models\Core;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Modules extends Model
{
    protected $table = 'sx_modules';
    public static function index() {
        $rows = DB::table('sx_modules')
                ->select('module_name', 'module_title', 'module_id', 'module_note', 'module_db', 'module_type', 'module_db_key', 'module_author','module_config','module_created')
                ->get();
        $data =[];        
        foreach($rows as $row){
            $module_config = json_decode( $row->module_config);
            if(isset($module_config->settings)){
                $row->settings = $module_config->settings ;
                
            } else {
                $row->settings = (object)[
                    'order_by'          => '',
                    'order_type'        => 'asc',
                    'display_row'       => '10' ,
                    'pagination_type'   => 'live'
                ];
                
            }
           // $row->settings = $module_config->settings ;
            $row->tables = $module_config->grid ;
            $row->forms = $module_config->forms ;             
            $row->primary_key   =  $module_config->primary_key ;
            $row->sql_group     =  $module_config->sql_group ;
            $row->sql_select    =  $module_config->sql_select ;
            $row->sql_where     =  $module_config->sql_where ;
            $row->table_db      =  $module_config->table_db ; 
            unset( $row->module_config );
            $row->access      =  self::permission( $row->module_id) ; 
            $data[] = $row ;
        }        
        return $data ;
    }
    public static function detail( $id ) {
        $rows = DB::table('sx_modules')
                ->select('module_name', 'module_title', 'module_id', 'module_note', 'module_db', 'module_type', 'module_db_key', 'module_author','module_config')
                ->where('module_name', $id )
                ->get();
        $data =[];        
        foreach($rows as $row){
            $module_config = json_decode( $row->module_config);
            $row->tables = $module_config->grid ;
            $row->forms = $module_config->forms ; 
            $row->setting = $module_config->settings ;  
            $row->sql_group     =  $module_config->sql_group ;
            $row->sql_select    =  $module_config->sql_select ;
            $row->sql_where     =  $module_config->sql_where ;
            $data  = $row ;
        }        
        return $data ;
    }
    public static function showTable()
    {
        $tables = DB::select('SHOW TABLES');

        $databaseName = 'Tables_in_' . env('DB_DATABASE');

        $data = array_map(function ($table) use ($databaseName) {
            return $table->$databaseName;
        }, $tables);

        return $data;
    }
    public static function findPrimarykey($table)
    {
        $query = "SHOW columns FROM `{$table}` WHERE extra LIKE '%auto_increment%'";
        $primaryKey = '';
        foreach (DB::select($query) as $key) {
            $primaryKey = $key->Field;
        }
        return $primaryKey;
    }
    public static function listAccess( $value ) {
        return (object) [ 
            'is_global'    => $value  ,
            'is_view'      => $value  ,
            'is_detail'    => $value  ,
            'is_add'       => $value  ,
            'is_edit'      => $value  ,
            'is_delete'    => $value  ,
            'is_print'     => $value  ,
            'is_csv'       => $value  ,
            'is_excel'     => $value  
        ];
    }
    public static function groups(  ) {
        return \DB::table('sx_groups')->get();  
    }
    public static function groupAccess( $module_id , $group_id ) {
        return \DB::table('sx_groups_access')->where(['module_id' => $module_id, 'group_id' => $group_id])->get();  
    }
    public static function permission( $module_id , $gid = '' ) { 
        $access = [];
        foreach(self::groups() as $group) {
            $group_access = self::groupAccess(  $module_id , $group->group_id);
            if( count($group_access) > 0 ) {
                $group_access = $group_access[0];
                $access[$group->group_id] = [
                    'group'     => $group->name ,
                    'id'        =>  $group->group_id ,
                    'access'    =>  json_decode($group_access->access_data,true)
                ];
            } else {
                $toAccess = ( $group->group_id == 1 ? '1' : '0' );
                $access[$group->group_id] = [
                    'group'     => $group->name ,
                    'id'        =>  $group->group_id ,
                    'access'    => self::listAccess(  $toAccess )
                ]; 
            }   
        }

        return (  $gid  !='' ?  $access[ $gid ]['access'] :   $access) ;

    }
}
