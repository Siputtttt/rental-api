<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Core\MenuService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public $config;
    public $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
        $this->config = (object) config('sximo');
    }
    public function index(Request $request)
    {

        $regEmail = base_path() . "/resources/views/emails/ActivationEmail.blade.php";
        $resetEmail = base_path() . "/resources/views/emails/otpPassword.blade.php";

        return response()->json([
            'data'      => $this->config,
            'emails'    => [
                'regEmail'     => file_get_contents($regEmail),
                'resetEmail'    =>     file_get_contents($resetEmail)
            ],
            'status' => 1
        ], 200);
    }
    public function store(Request $request)
    {
        $task = $request->action_task;
        switch ($task) {
            default:
                return $this->store_setting($request);
                break;

            case 'emails':
                return $this->store_emails($request);
                break;
        }
    }

    public function store_setting(Request $request)
    {
        $posts = (object) $request->all();

        if (isset($posts->images)) {
            $check_images = str_contains($posts->images, 'http');
            if (!$check_images) {
                $pics         = str_replace("data:image/png;base64,", "", $posts->images);
                $decoded_data = base64_decode($pics);
                $file_name = 'logo.png';
                Storage::disk('public')->put($file_name, $decoded_data);
                $posts->cnf_logo = asset('storage/' . $file_name);
            }
        }
        File::put(base_path('config/sximo.php'), '<?php ');
        $toWrite = <<<PHP
            return [
                'cnf_appname' 			=> '{$posts->cnf_appname}',
                'cnf_appdesc' 			=> '{$posts->cnf_appdesc}',
                'cnf_comname' 			=> '{$posts->cnf_comname}',
                'cnf_email' 			=> '{$posts->cnf_email}',
                'cnf_metakey' 			=> '{$posts->cnf_metakey}',
                'cnf_metadesc' 		    => '{$posts->cnf_metadesc}',
                'cnf_group' 			=> '{$posts->cnf_group}',
                'cnf_activation' 		=> '{$posts->cnf_activation}',
                'cnf_multilang' 		=> '{$posts->cnf_multilang}',
                'cnf_lang' 			    => '{$posts->cnf_lang}',
                'cnf_regist' 			=> '{$posts->cnf_regist}',
                'cnf_front' 			=> '{$posts->cnf_front}',
                'cnf_recaptcha' 		=> '{$posts->cnf_recaptcha}',
                'cnf_theme' 			=> '{$posts->cnf_theme}',
                'cnf_backend' 			=> '{$posts->cnf_backend}',
                'cnf_recaptchapublickey' => '{$posts->cnf_recaptchapublickey}',
                'cnf_recaptchaprivatekey' => '{$posts->cnf_recaptchaprivatekey}',
                'cnf_mode' 			=> '{$posts->cnf_mode}',
                'cnf_logo' 			=> '{$posts->cnf_logo}',
                'cnf_allowip' 			=> '{$posts->cnf_allowip}',
                'cnf_restrictip' 		=> '{$posts->cnf_restrictip}',
                'cnf_mail' 			=> '{$posts->cnf_mail}',
                'cnf_maps' 			=> '{$posts->cnf_maps}',
                'cnf_date' 			=> '{$posts->cnf_date}',
            ];
                    
        PHP;
        File::append((base_path('config/sximo.php')), $toWrite);
        return response()->json([
            'message'      => 'Data has been saved successful!',
            'status' => 1
        ], 200);
    }

    public function store_emails(Request $request)
    {

        $regEmail = base_path() . "/resources/views/emails/ActivationEmail.blade.php";
        $resetEmail = base_path() . "/resources/views/emails/otpPassword.blade.php";

        $fp = fopen($regEmail, "w+");
        fwrite($fp, $request->regEmail);
        fclose($fp);

        $fp = fopen($resetEmail, "w+");
        fwrite($fp, $request->resetEmail);
        fclose($fp);

        return response()->json([
            'message'      => 'Data has been saved successful!',
            'status' => 1
        ], 200);
    }


    public function info(Request $request)
    {
        $socialite = [];

        foreach (config('services') as $key => $value) {
            if (is_array($value) && array_key_exists('client_id', $value)) {
                $socialite[$key] = $value['client_id'] != null;
            }
        }

        $data = [
            'appname'   => $this->config->cnf_appname,
            'appdesc'   => $this->config->cnf_appdesc,
            'frontend'   => $this->config->cnf_front,
            'registration'   => $this->config->cnf_regist,
            'logo'      => $this->config->cnf_logo,
            'socialite' => $socialite
        ];
        return response()->json([
            'data'      => $data,
            'status' => 1
        ], 200);
    }
}
