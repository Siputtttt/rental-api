<?php

namespace App\Http\Controllers\Core; 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use App\Models\Core\User; 

class AuditController extends Controller
{
    public $sximo;
    public function __construct()
    {
        $this->sximo = (object) config('sximo');
    }

    public function index(Request $request)
    {
        $rows = \DB::table('sx_logs')->get();   
        return response()->json([
            'status'      => 1 , 
            'data'         => [
                'rows'  => $rows 
            ] 
        ],200);       
         
    }

    public function store(Request $request)
    {
        
    }
 
    
}
