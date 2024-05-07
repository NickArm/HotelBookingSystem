<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            //Admin
            [
                'name' => 'Admin',
                'email' => 'armenisnick@gmail.com',
                'password' => Hash::make('n@261186'),
                'role' => 'admin',
                'status' => 'active',
            ],
            //User
            [
                'name' => 'User',
                'email' => 'user@gmail.com',
                'password' => Hash::make('n@261186'),
                'role' => 'user',
                'status' => 'active',
            ],

        ]);

    }
}
