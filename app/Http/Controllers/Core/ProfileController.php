<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Core\User;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public $sximo;
    public function __construct()
    {
        $this->sximo = (object) config('sximo');
    }

    public function index(Request $request)
    {
        return response()->json([
            'data'      =>  $request->user(),
            'status' => 1
        ], 200);
    }

    public function store(Request $request)
    {
        switch ($request->task) {
            default:
                break;

            case 'savePersonalInfo':
                return $this->savePersonalInfo($request);
                break;

            case 'changePassword':
                return $this->changePassword($request);
                break;
        }
    }

    public function savePersonalInfo(Request $request)
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
                'group_id'              => $isUpdate ? 'nullable' : 'required',
                'active'                => $isUpdate ? 'nullable' : 'required',
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

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
            'new_password_confirmation' => 'required|same:new_password',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password successfully updated.',
        ]);
    }
}
