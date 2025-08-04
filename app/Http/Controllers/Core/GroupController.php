<?php

namespace App\Http\Controllers\Core;

use App\Models\Core\Group;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GroupController extends Controller
{
    public $data;

    public function __construct()
    {
        $this->data = [];
    }

    public function index(Request $request)
    {
        $this->data['group'] = Group::all();

        return response()->json([
            'status'   => true,
            'message'  => 'successfull to get data',
            'data'     => $this->data
        ]);
    }
    public function show(Request $request, $task)
    {
        switch ($task) {
            case 'edit':
                $this->data['user'] = Group::find($request->group_id);

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

            case 'save':
                return $this->save($request);
                break;

            case 'delete':
                return $this->delete($request);
                break;
        }
    }

    public function save(Request $request)
    {

        try {
            $fields = $request->validate([
                'name'        => 'required|max:255',
                'backend'     => 'required|max:255',
                'description' => 'nullable',
                'level'       => 'required|max:255',
            ]);

            if ($request->group_id) {
                $user = Group::find($request->group_id);
                $user->update($fields);
            } else {
                $user = Group::create($fields);
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

    public function delete($request)
    {
        try {
            $data = Group::find($request->group_id);

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
