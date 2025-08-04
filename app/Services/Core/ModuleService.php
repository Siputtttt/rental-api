<?php

namespace App\Services\Core;

use App\Models\Core\Modules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;

class ModuleService
{
    public $statement;
    public $primaryKey;
    public $params;
    public $columns;
    public $tables;
    public $forms;
    public $settings;
    public $rebuild ;

    public function prepareModule(  $params   ) {
        //print_r($params);
        $this->params = $params ;
        $this->rebuild = ( $params->module_id !='' ? true : false ) ; 
        $this->buildStatment(  $this->rebuild );
       
        $this->columns();
        $this->config();
        return true ;

       // return [  $this->statement ,   $this->tables ,   $this->tables ]  ;       
    }
    public function buildModule( $is_edit = false ) {
        // Insert Module Info into Database
        if($is_edit == false)
            $this->insertModule();
        /* Generate Laravel Files */
        $this->generateController() ;
        $this->generateModel() ;
        $this->generateRoutes() ;
         /* Generate Vues Files */
        $this->generateVue();
        $this->generateVueRoutes();

        return [  $this->statement ,   $this->columns ,  $this->tables ,   $this->forms ] ;
    }
    public function insertModule() {
        $module_config = [
            'sql_select'    =>  $this->statement['sql_select'],
            'sql_where'     =>  $this->statement['sql_where'],
            'sql_group'     =>  $this->statement['sql_group'],
            'primary_key'   =>  $this->primaryKey ,
            'table_db'      => $this->params->module_db,
            'grid'          => $this->tables ,
            'forms'          => $this->forms ,
            'settings'          => $this->settings 
        ];
        if(  $this->params->module_type =='blank') {
            $module_config['grid'] = [];
            $module_config['forms'] = [];
        }

        \DB::table('sx_modules')->insert([
            'module_title'   => $this->params->module_title,
            'module_note'   => $this->params->module_note,
            'module_name'   => $this->params->module_name,
            'module_db'     => $this->params->module_db,
            'module_type'   => $this->params->module_type,
            'module_desc'   => $this->params->module_note,
            'module_config' => json_encode($module_config),
            'module_db_key' => $this->primaryKey 
        ]);

    }
    public function buildStatment(  $rebuild = false  ) {

        $key = Modules::findPrimarykey( $this->params->module_db );
        if( $rebuild ) {
            $sql = Modules::detail( $this->params->module_name );
            $this->statement = [
                'sql_select' => $sql->sql_select,
                'sql_where' => $sql->sql_where,
                'sql_group' => $sql->sql_group
            ]; 

        } else {
            $columns = DB::select("SHOW COLUMNS FROM " . $this->params->module_db );
            $select =  " SELECT  ".$this->params->module_db.".* FROM ".$this->params->module_db ;
            $where = " WHERE " . $this->params->module_db . "." . $key . " IS NOT NULL";
            if ($key != '') {
                $where     = " WHERE " . $this->params->module_db . "." . $key . " IS NOT NULL";
            } else {
                $where  = '';
            }
            $this->statement = [
                'sql_select' => $select,
                'sql_where' => $where,
                'sql_group' => ''
            ]; 
        }
        $this->primaryKey = $key ;
        
        
    }
    public function columns() {

        $pdo = \DB::getPdo();
		$res = $pdo->query(   $this->statement['sql_select'] .' '. $this->statement['sql_where'] .' '. $this->statement['sql_group']  );
		$i = 0;	$columns=array();	
		while ($i < $res->columnCount()) 
		{
			$columns[] =$res->getColumnMeta($i);		
			$i++;
		} 
        $this->columns = (object) $columns ;
    }
    public function config() {
        $table = [];
        foreach(  $this->columns as $col) {
            $table[] = [
                "field"     => $col['name'],
                "alias"     => $col['table'],
                "label"     => ucwords(str_replace('_', ' ', $col['name'] )),
                "language"    => array(),
                "search"     => '1',
                "download"     => '1',
                "align"     => 'left',
                "view"         => '1',
                "detail"      => '1',
                "sortable"     => '0', 
                'hidden'    => '0', 
                "width"     => '100',
                "conn"          => array('valid' => '0', 'db' => '', 'key' => '', 'display' => ''),
                "format_as"     => '',
                "format_value"  => '',

            ];
        }
        $this->tables = $table ;

        $forms = [];
        foreach(  $this->columns as $col) {            
            $forms[] = [
                "field"             =>  $col['name'],
                "alias"             =>  $col['table'],
                "label"             => ucwords(str_replace('_',' ',$col['name'])),
                "language"          => array(),
                'required'          => '',
                'view'              => '1',
                'type'              => self::formType($col['native_type']),
                'add'               => '1',
                'edit'              => '1',
                'search'            => '1',
                'size'              => '12' ,
                "option"            => '' 
            ];
        }
        $this->forms = $forms ;   

        $settings = [ 
            'order_by'              => $this->primaryKey ,
            'ordery_type'           => 'asc',
            'display_row'           => '10',
            'pagination_type'       => 'live'  
        ]; 
        $this->settings = $settings ;
        
    }
    public function rebuildColumns( $statement , $tables , $forms , $dbtable  ) {

        $pdo = \DB::getPdo();
		$res = $pdo->query(   $statement['sql_select'] .' '. $statement['sql_where'] .' '. $statement['sql_group']  );
		$i = 0;	$columns=array();	
        $new_tables = [];
        $new_forms = [];
		while ($i < $res->columnCount()) 
		{
            $cols = $res->getColumnMeta($i) ;
            /* Block for update columns table */
            $onTables = $this->configTable($cols['name'] , $cols['table'] , $cols['native_type']  );
            foreach($tables as $table) {
                if($table->field == $cols['name']  && $table->alias == $cols['table'] ) {
                    $onTables = $table ;
                }
            }
             $new_tables[] = $onTables;
            /* End Block for update columns table */
           
            /* Block for update columns forms */
            if( $dbtable == $cols['table']) {
                $onForms = $this->configForm($cols['name'] , $cols['table'] , $cols['native_type']  );
                foreach($forms as $form) {
                    if($form->field == $cols['name']  && $form->alias == $cols['table'] ) {
                        $onForms = $form ;
                    }
                }
                $new_forms[] = $onForms;
            }
            /* End Block for update columns forms */


			$i++;
		} 
        return ['tables' => $new_tables , 'forms' => $new_forms ] ;
       
    }

    function configTable( $name , $alias , $type ) {
        return [
             "field"                =>  $name  ,
                "alias"             =>  $alias,
                "label"             => ucwords(str_replace('_',' ', $name )),
                "language"    => array(),
                "search"     => '1',
                "download"     => '1',
                "align"     => 'left',
                "view"         => '1',
                "detail"      => '1',
                "sortable"     => '0', 
                'hidden'    => '0', 
                "width"     => '100',
                "conn"          => array('valid' => '0', 'db' => '', 'key' => '', 'display' => ''),
                "format_as"     => '',
                "format_value"  => '',
        ];
    }

    function configForm( $name , $alias , $type ) {
        return [
                "field"             =>  $name ,
                "alias"             =>  $alias,
                "label"             => ucwords(str_replace('_',' ', $name  )),
                "language"          => array(),
                'required'          => '',
                'view'              => '1',
                'type'              => self::formType( $name ),
                'add'               => '1',
                'edit'              => '1',
                'search'            => '1',
                'size'              => '12' ,
                "option"            => '' 
            ];
        
    }

    function formType( $type )
    {
        switch($type)
        {
            default: $type = 'text'; break;
            case 'DATE';        $type = 'date'; break;
            case 'DATETIME';    $type = 'datetime'; break;
            case 'STRING';      $type = 'text'; break;
            case 'VAR_STRING'; $type = 'text'; break; 
        }
        return $type;
    
    } 
    public function generateController() {
        $controllerClassName = ucfirst($this->params->module_name) . 'Controller';
        $modelClassName = ucfirst($this->params->module_name);

        $template = $this->params->module_type.".Controller";
        $controllerTemplate = View::make($template, [
            'controllerClassName' => $controllerClassName,
            'moduleName' => $this->params->module_name,
            'modelClassName' => $modelClassName,
            'moduleDB' =>$this->params->module_db,
            'moduleDBKey' => $this->primaryKey,
        ])->render();

        $controllerPath = app_path("Http/Controllers/{$controllerClassName}.php");
        file_put_contents($controllerPath, $controllerTemplate);  
    }

    public function generateModel() { 

            $modelClassName = ucfirst($this->params->module_name);    
            $template =  $this->params->module_type  .".Model";
            $modelTemplate = View::make($template, [
                'moduleName' => $modelClassName,
                'moduleDB' => $this->params->module_db,
                'moduleDBKey' => $this->primaryKey,
                'fillableFields' => '' ,
                'stateSelect'   => $this->statement['sql_select'] ,
                'stateWhere'   => $this->statement['sql_where'] ,
                'stateGroup'   => $this->statement['sql_group'] ,

            ])->render();
    
            $modelPath = app_path("Models/{$modelClassName}.php");
            file_put_contents($modelPath, $modelTemplate);
    
        
    }
    public function generateRoutes( )
    {
        $rows = \DB::table('sx_modules')->get();        
        $routePath = base_path('routes/modules.php');
        File::put(base_path('routes/modules.php'),'<?php ');
        foreach($rows as $row) {
            $module = ucfirst($row->module_name);
            $routeEntry = <<<PHP

            Route::resource('{$row->module_name}', App\Http\Controllers\\{$module}Controller::class); 

            PHP;

            File::append($routePath, $routeEntry);
        }
    }

    public function generateVue( )
    {
        $modelClassName = ucfirst($this->params->module_name);    
        $template =  $this->params->module_type  .".Index";
        $vueTemplate = View::make($template, [
            'moduleName' => $this->params->module_name,
            'moduleTitle' => $this->params->module_title,
            'moduleDB' => $this->params->module_db,
            'moduleDBKey' => $this->primaryKey 

        ])->render();
        $folder_template = env('APP_VUE')."views/modules/".$this->params->module_name ;
        if( !is_dir( $folder_template )) {
            mkdir($folder_template, 0755, true);
        }

        $vuePath =   env('APP_VUE')."views/modules/{$this->params->module_name}/{$modelClassName}.vue"; 
        file_put_contents($vuePath, $vueTemplate);
    }
    public function generateVueRoutes( )
    {
        $rows = \DB::table('sx_modules')->get();        
        $routePath =  env('APP_VUE')."router/module.js";  
        File::put(  env('APP_VUE')."router/module.js"," export default [ ");
        foreach($rows as $row) {
            $module = ucfirst($row->module_name);
            $routeEntry = <<<PHP

             {
                path: '/{$row->module_name}',
                name: '{$row->module_name}',
                component: () => import(`@/views/modules/{$row->module_name}/{$module}.vue`),
                meta: { layout: 'dashboard', public: true, auth: false },
            },

            PHP;

            File::append($routePath, $routeEntry);
        }
        File::append($routePath," ] ");
    }

    public function removeModule($moduleName)
    {
        self::removeController($moduleName);
        self::removeModel($moduleName);
        self::removeVue($moduleName); 

        \DB::table('sx_modules')->where('module_name', $moduleName)->delete();
        $this->generateRoutes();
        $this->generateVueRoutes();
        
    }

    public function removeController($moduleName)
    {
        $controllerPath = app_path("Http/Controllers/".ucfirst($moduleName)."Controller.php");
        if (File::exists($controllerPath)) {
            File::delete($controllerPath);
        }
    }

    public function removeModel($moduleName)
    {
        $modelPath = app_path("Models/".ucfirst($moduleName).".php");
        if (File::exists($modelPath)) {
            File::delete($modelPath);
        }
    }

    public function removeVue($moduleName)
    {
        $vuePath = env('APP_VUE')."views/modules/". $moduleName ."/".ucfirst($moduleName).".vue"; 
        if (File::exists($vuePath)) { 
            File::delete($vuePath);
        }
    }

}
