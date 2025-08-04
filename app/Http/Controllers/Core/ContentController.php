<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\Core\Content;
use App\Models\Core\Categories;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    private $data;

    public function __construct()
    {
        $this->data = [];
    }

    public function index(Request $request)
    {;

        $filter = [
            'search' => $request->input('s'),
            'page' => $request->input('p') ?: 1,
            'limit' => $request->input('l') ?: 10,
            'categories_id' => $request->input('cid') ?: null,
        ];

        $this->data['content'] = Content::getContent($filter);
        $this->data['categories'] = Categories::all()->map(function ($category) {
            $category->image = $category->image ? asset('storage/' . $category->image) : null;
            return $category;
        });

        $total_content = Content::count();
        $total_page = ceil($total_content / max(1, $filter['limit']));

        $this->data['pagination_content'] = [
            'current_page' => $filter['page'],
            'total' => $total_content,
            'per_page' => $filter['limit'],
            'last_page' => $total_page,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Successfully retrieved contents.',
            'data' => $this->data
        ]);
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
                'categories_id' => 'nullable|integer',
                'title' => 'nullable|string|max:255',
                'sinopsis' => 'nullable|string',
                'note' => 'nullable',
                'acces_data' => 'nullable|string',
                'allow_guest' => 'nullable|in:0,1',
                'labels' => 'nullable|string|max:100',
                'image' => 'nullable|string',
            ]);

            $baseSlug = Str::slug($fields['title']);
            $slug = $baseSlug;
            $counter = 1;

            while (Content::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $fields['slug'] = $slug;

            if (!empty($fields['image']) && Str::startsWith($fields['image'], 'data:image')) {
                $fields['image'] = $this->saveBase64Image($fields['image'], 'content', 7);
            }
            if ($request->id) {
                $content = Content::findOrFail($request->id);

                if (!empty($content->image)) {
                    Storage::disk('public')->delete($content->image);
                }

                $content->update($fields);
            } else {
                $content = Content::create($fields);
            }


            return response()->json([
                'status' => true,
                'message' => 'Content created successfully.',
                'data' => $content
            ]);
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
        $data = Content::find($request->id);
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
            'status' => true,
            'message' => 'Content deleted successfully.'
        ]);
    }
}
