<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sximo extends Model
{
    use HasFactory;

    public function __construct() 
	{
		parent::__construct();

    }	

    public static function auditLogs( $userid , $module , $action , $posts ) {
        try {
            \DB::table('sx_logs')->insert([
                'data'      => json_encode($posts),
                'module'    => $module ,
                'action'    => $action ,
                'inserted'  => date("Y-m-d H:i:s"),
                'user_id'   => $userid 
            ]);
        } catch(Exception $e) {

        }
    }

     
}
