<?php

namespace App\Models\Core;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Pagination\LengthAwarePaginator;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'sx_users';
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'active',
        'email',
        'password',
        'group_id',
        'avatar',
        'last_activity',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_activity' => 'datetime',
        ];
    }

    public static function getUser($perPage = 10, $currentPage = 1, $search = null)
    {
        $offset = ($currentPage - 1) * $perPage;
        $params = [];
        $whereClause = '';

        if (!empty($search)) {
            $keyword = '%' . $search . '%';
            $whereClause = "WHERE a.username LIKE ? OR a.first_name LIKE ? OR a.last_name LIKE ? OR a.email LIKE ?";
            $params = [$keyword, $keyword, $keyword, $keyword];
        }

        $query = "
        SELECT SQL_CALC_FOUND_ROWS a.*, b.name AS group_name, b.level AS group_level
        FROM sx_users a
        LEFT JOIN sx_groups b ON a.group_id = b.group_id
        $whereClause
        LIMIT ?, ?
    ";

        $params[] = $offset;
        $params[] = $perPage;

        $rows = DB::select($query, $params);
        $total = DB::select("SELECT FOUND_ROWS() as total")[0]->total;

        return new LengthAwarePaginator(
            $rows,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public static function checkUserByEmail($email)
    {
        $query = DB::select("SELECT username, email, first_name, group_id, active FROM sx_users WHERE email = '{$email}' LIMIT 1");

        return $query;
    }
}
