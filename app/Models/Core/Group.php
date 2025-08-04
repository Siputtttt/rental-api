<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class Group extends Model
{
    protected $table = 'sx_groups';

    protected $primaryKey = 'group_id';

    protected $fillable = [
        'name',
        'description',
        'backend',
        'level'
    ];

    public $timestamps = true;

    public static function getGroup($perPage = 10, $currentPage = 1, $search = null)
    {
        $offset = ($currentPage - 1) * $perPage;
        $params = [];
        $searchClause = "";

        if (!empty($search)) {
            $keyword = '%' . $search . '%';
            $searchClause = "WHERE name LIKE ? OR description LIKE ? OR backend LIKE ? OR level LIKE ?";
            $params = [$keyword, $keyword, $keyword, $keyword];
        }

        // Ambil data
        $query = "SELECT * FROM sx_groups $searchClause LIMIT ? OFFSET ?";
        $paramsForData = array_merge($params, [$perPage, $offset]);
        $rows = DB::select($query, $paramsForData);

        // Ambil total count
        $countQuery = "SELECT COUNT(*) as total FROM sx_groups $searchClause";
        $count = DB::select($countQuery, $params);
        $total = $count[0]->total ?? 0;

        // Kembalikan pagination
        return new LengthAwarePaginator(
            $rows,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}
