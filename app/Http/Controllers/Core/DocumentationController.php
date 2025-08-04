<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Core\Categories;
use App\Models\Core\Content;


class DocumentationController extends Controller
{
    private $data;
    public function __construct()
    {
        $this->data = [];
    }

    public function docs(Request $request)
    {
        $categories = Categories::all()->map(function ($category) {
            return [
                'id' => $category->id,
                'parent_id' => $category->parent_id,
                'name' => $category->name,
                'slug' => $category->slug,
                'desc' => $category->desc,
                'image' => $category->image ? asset('storage/' . $category->image) : null,
                'active' => $category->active,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,      
                'children' => [],
                'content' => Content::where('categories_id', $category->id)->get()->map(function ($content) {
                    $content->image = $content->image ? asset('storage/' . $content->image) : null;
                    return $content;
                }),
            ];
        });

        $categoriesById = collect($categories)->keyBy('id')->toArray();

        foreach ($categoriesById as $id => $category) {
            if ($category['parent_id'] && isset($categoriesById[$category['parent_id']])) {
                $categoriesById[$category['parent_id']]['children'][] = $category;
                unset($categoriesById[$id]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Documentation',
            'data' => [
                'categories' => array_values($categoriesById),
            ],
        ]);
    }


    public function docsContent($content)
    {
        $this->data['content'] = Content::where('slug', $content)->first();
        if (!$this->data['content']) {
            return response()->json([
                'status' => false,
                'message' => 'Content not found',
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Documentation',
            'data' => $this->data,
        ]);
    }
}
