<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SxUsersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sx_users')->insert([
            'group_id' => 1,
            'username' => 'admin',
            'first_name' => 'Muhammad',
            'last_name' => 'Putra',
            'active' => '1',
            'email' => 'muhammadputra752@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('konoha'), // hashed password
            'remember_token' => null,
            'avatar' => null,
            'otp' => null,
            'otp_token' => null,
            'last_activity' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
