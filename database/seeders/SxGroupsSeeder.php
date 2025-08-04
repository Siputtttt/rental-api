<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SxGroupsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sx_groups')->insert([
            [
                'name' => 'Super Admin',
                'description' => 'tes',
                'level' => 1,
                'backend' => '0',
                'created_at' => '2025-04-22 21:32:50',
                'updated_at' => '2025-04-28 10:26:54',
            ],
            [
                'name' => 'Admin',
                'description' => null,
                'level' => 2,
                'backend' => '0',
                'created_at' => '2025-04-23 08:57:28',
                'updated_at' => '2025-04-23 08:57:28',
            ],
            [
                'name' => 'User',
                'description' => null,
                'level' => 3,
                'backend' => '0',
                'created_at' => '2025-04-23 08:57:34',
                'updated_at' => '2025-04-23 08:57:34',
            ],
        ]);
    }
}
