<?php

namespace App\Http\Controllers\Core;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Core\Categories;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;

class CategoriesController extends Controller
{
    public $data;

    public function __construct()
    {
        $this->data = [];
    }

    public function index(Request $request)
    {
        $this->data['categories'] = Categories::all()->map(function ($category) {
            $category->image = $category->image ? asset('storage/' . $category->image) : null;
            return $category;
        });
        
        return response()->json([
            'status'  => true,
            'message' => 'successfully retrieved',
            'data'    => $this->data
        ], 200);
    }

    public function show(Request $request, $task)
    {
        switch ($task) {
            case 'edit':
                $this->data['categories'] = Categories::find($request->id);

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
                'name' => 'required',
                'desc' => 'required',
                'image' => 'nullable|string',
                'active' => 'required',
                'parent_id' => 'nullable|integer',
            ]);

            $baseSlug = Str::slug($fields['name']);
            $slug = $baseSlug;
            $counter = 1;

            while (Categories::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $fields['slug'] = $slug;

            if (!empty($fields['image']) && Str::startsWith($fields['image'], 'data:image')) {
                $fields['image'] = $this->saveBase64Image($fields['image'], 'categories', 2);
            }

            if ($request->id) {
                $category = Categories::findOrFail($request->id);

                if (!empty($category->image)) {
                    Storage::disk('public')->delete($category->image);
                }

                $category->update($fields);
            } else {
                Categories::create($fields);
            }

            return response()->json([
                'status'  => true,
                'message' => 'successfully saved',
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
            $data = Categories::find($request->id);

            if (!$data) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Nothing comes delete',
                    'error'   => 'Invalid ID'
                ], 404);
            }

            if ($data->image) {
                Storage::disk('public')->delete($data->image);
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
