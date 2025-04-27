<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $roles = ['Admin', 'Editor', 'User'];
        $firstNames = ['John', 'Jane', 'Michael', 'Emily', 'David', 'Sarah', 'Robert', 'Jennifer', 'William', 'Lisa'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Miller', 'Davis', 'Garcia', 'Rodriguez', 'Wilson'];

        for ($i = 0; $i < 20; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $name = "$firstName $lastName";
            $email = Str::lower($firstName).'.'.Str::lower($lastName).($i > 0 ? $i : '').'@example.com';
            // $role = $roles[array_rand($roles)];

            DB::table('users')->insert([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
                // 'role' => $role,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('users')->insert([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            // 'role' => 'Admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}