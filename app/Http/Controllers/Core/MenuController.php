<?php

namespace App\Http\Controllers\Core;

use App\Models\Core\Group;
use App\Models\Core\Menu;
use App\Models\Core\Modules;
use Illuminate\Http\Request;
use App\Services\Core\MenuService;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class MenuController extends Controller
{
    public $data;
    public $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
        $this->data = [];
    }

    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $groupId = $user->group_id;

            $menus = $this->menuService->getMenu($groupId);

            return response()->json([
                'status'  => true,
                'message' => 'successfully retrieved',
                'data'    => $menus
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Unexpected Error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        try {
            $this->data['module'] = Modules::index();
            $this->data['menu'] = $this->menuService->getMenu();
            $this->data['group'] = Group::all();

            return response()->json([
                'status'  => true,
                'message' => 'successfully retrieved',
                'data'    => $this->data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Unexpected Error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $task)
    {
        switch ($task) {
            case 'edit':
                $this->data['menu'] = Menu::find($request->id);
                $this->data['menu']->access_data = json_decode($this->data['menu']->access_data, true);

                return response()->json([
                    'status'   => true,
                    'message'  => 'successfull to get data',
                    'data'     => $this->data
                ]);
        }
    }

    public function store(Request $request)
    {
        switch ($request->task) {
            default:
                break;

            case 'save_menu':
                return $this->save_menu($request);
                break;

            case 'save_position':
                return $this->save_position($request);
                break;

            case 'delete':
                return $this->delete($request);
                break;
        }
    }
    public function save_menu(Request $request)
    {
        try {
            $fields = $request->validate([
                'menu_name' => 'required',
                'menu_type' => 'required',
                'menu_icons' => 'required',
                'position' => 'required',
                'active' => 'required',
                'menu_url' => 'nullable',
                'menu_icons' => 'nullable',
                'module' => 'nullable',
                "allow_guest" => 'required',
                'module' => 'nullable',
                'access_data' => 'nullable|array'
            ]);
            
            $fields['access_data'] = json_encode($fields['access_data']);

            if ($request->menu_id) {
                $user = Menu::find($request->menu_id);
                $user->update($fields);
            } else {
                $user = Menu::create($fields);
            }

            return response()->json([
                'status'  => true,
                'message' => 'successfully saved',
                'data'    => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Unexpected Error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function save_position(Request $request)
    {
        try {
            $menus = $request->menu;

            $data = $this->menuService->saveNestedMenus($menus);

            return response()->json([
                'status'  => true,
                'message' => 'successfully saved',
                'data'    => $data
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Unexpected Error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function delete($request)
    {
        try {
            $data = Menu::find($request->menu_id);

            if (!$data) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Nothing comes delete',
                    'error'   => 'Invalid ID'
                ], 404);
            }

            $data->delete();

            return response()->json([
                'status'  => true,
                'message' => 'successfully deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Unexpected Error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
