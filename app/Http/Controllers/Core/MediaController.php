<?php

namespace App\Http\Controllers\Core;
require '../vendor/autoload.php';
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Core\User;
use Illuminate\Support\Facades\Hash; 
use Ozdemir\VueFinder\Vuefinder;
use League\Flysystem\Local\LocalFilesystemAdapter;

class MediaController extends Controller
{
    public $sximo;
    public function __construct()
    {
        $this->sximo = (object) config('sximo');
    }

    public function index(Request $request)
    {
        $vuefinder = new VueFinder([
            'local' => new LocalFilesystemAdapter(dirname(__DIR__).'../../../../storage/app/public') 
        ]);
        $config = [
            'publicLinks' => [
                'local://public' => url(),
            ],
        ];
        return  $vuefinder->init($config)  ; 
    }

    public function store(Request $request)
    {
        $vuefinder = new VueFinder([
            'local' => new LocalFilesystemAdapter(dirname(__DIR__).'../../../../storage/app/public') 
        ]);
        $config = [
            'publicLinks' => [
                'local://public' => url(),
            ],
        ];
        return  $vuefinder->init($config)  ; 
    }
 
    
}
