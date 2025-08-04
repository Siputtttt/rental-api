<?php

namespace App\Http\Controllers\Core;

use App\Models\Core\User;
use App\Models\Core\Group;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    public $data;

    public function __construct()
    {
        $this->data = [];
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $page = $request->input('page', 1);
        $limit = 10;

        $users = User::getUser($limit, $page, $search);

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched data',
            'data' => [
                'users' => $users->items(),
                'pagination_users' => [
                    'current_page' => $users->currentPage(),
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'last_page' => $users->lastPage(),
                ],
            ],
        ]);
    }

    public function show(Request $request, $task)
    {
        switch ($task) {
            case 'edit':
                $this->data['user'] = User::find($request->id);
                return response()->json([
                    'status'   => true,
                    'message'  => 'successfull to get data',
                    'data'     => $this->data
                ]);
                break;

            case 'groups':
                $search = $request->input('search');
                $page = $request->input('page', 1);
                $limit = 10;

                $groups = Group::getGroup($limit, $page, $search);
                return response()->json([
                    'status' => true,
                    'message' => 'Successfully fetched data',
                    'data' => [
                        'groups' => $groups->items(),
                        'pagination_groups' => [
                            'current_page' => $groups->currentPage(),
                            'total' => $groups->total(),
                            'per_page' => $groups->perPage(),
                            'last_page' => $groups->lastPage(),
                        ],
                    ],
                ]);
                break;

            default:
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid task',
                ], 400);
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
            $isUpdate = $request->has('id');

            $rules = [
                'username'              => 'required|max:255',
                'first_name'            => 'required|max:255',
                'last_name'             => 'required|max:255',
                'email'                 => [
                    'required',
                    'email',
                    $isUpdate
                        ? Rule::unique('sx_users')->ignore($request->id)
                        : 'unique:sx_users,email'
                ],
                'password'              => $isUpdate ? 'nullable|min:6|confirmed' : 'required|min:6|confirmed',
                'password_confirmation' => $isUpdate ? 'nullable|same:password' : 'required|same:password',
                'avatar' => 'nullable|string',
                'group_id'              => 'required',
                'active'                => 'required',
            ];

            $fields = $request->validate($rules);

            if (isset($fields['active'])) {
                $fields['active'] = (string) $fields['active'];
            }

            if ($request->filled('avatar')) {
                $base64Image = $request->input('avatar');

                $sizeInBytes = (int) (strlen($base64Image) * 3 / 4);
                $maxSize = 2 * 1024 * 1024;

                if ($sizeInBytes > $maxSize) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Image size must be less than 2MB',
                    ], 422);
                }

                if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                    $image = substr($base64Image, strpos($base64Image, ',') + 1);
                    $image = base64_decode($image);
                    $extension = strtolower($type[1]);

                    if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Invalid image type',
                        ], 422);
                    }

                    $fileName = uniqid() . '.' . $extension;
                    $filePath = 'avatars/' . $fileName;

                    if ($isUpdate) {
                        $existingUser = User::find($request->id);
                        if ($existingUser && $existingUser->avatar && Storage::disk('public')->exists($existingUser->avatar)) {
                            Storage::disk('public')->delete($existingUser->avatar);
                        }
                    }

                    Storage::disk('public')->put($filePath, $image);
                    $fields['avatar'] = $filePath;
                }
            }

            if ($isUpdate) {
                $user = User::findOrFail($request->id);

                if (empty($fields['password'])) {
                    unset($fields['password'], $fields['password_confirmation']);
                }

                $user->update($fields);
                $statusCode = 200;
            } else {
                $user = User::create($fields);
                $statusCode = 201;
            }

            return response()->json([
                'status'  => true,
                'message' => 'Successfully saved',
                'data'    => $user
            ], $statusCode);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($request)
    {
        try {
            $user = User::find($request->id);

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'User not found',
                    'error'   => 'Invalid user ID'
                ], 404);
            }

            $user->delete();

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
