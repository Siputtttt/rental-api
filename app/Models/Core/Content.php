<?php

namespace App\Models\Core;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Content extends Model
{
    use HasFactory;

    protected $table = 'sx_content';
    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'categories_id',
        'title',
        'slug',
        'sinopsis',
        'note',
        'acces_data',
        'allow_guest',
        'labels',
        'image',
    ];

    public static function getContent($filter)
    {
        $search = '';
        $page = 1;
        $limit = 1;

        if (isset($filter['search']) && $filter['search']) {
            $search .= "AND co.title LIKE '%" . addslashes($filter['search']) . "%' ";
        }
        if (isset($filter['page']) && is_numeric($filter['page'])) {
            $page = (int) $filter['page'];
        }
        if (isset($filter['limit']) && is_numeric($filter['limit'])) {
            $limit = (int) $filter['limit'];
        }

        if (isset($filter['categories_id']) && is_numeric($filter['categories_id'])) {
            $search .= "AND co.categories_id = " . (int) $filter['categories_id'] . " ";
        }

        $offset = ($page - 1) * $limit;

        $data = DB::select("SELECT 
                            co.*, ca.name AS name_categories 
                        FROM sx_content co
                        RIGHT JOIN sx_categories ca ON ca.id = co.categories_id
                        WHERE co.id IS NOT NULL $search
                        ORDER BY co.id ASC
                        LIMIT $limit OFFSET $offset");

        foreach ($data as $key => $value) {
            $data[$key]->image = $value->image ? asset('storage/' . $value->image) : null;
        }


        return $data;
    }
}
