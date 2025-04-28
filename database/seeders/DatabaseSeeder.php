<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create 20 permissions
        $permissionNames = [
            'view users', 'edit users', 'delete users', 'create users',
            'view roles', 'edit roles', 'delete roles', 'create roles',
            'view posts', 'edit posts', 'delete posts', 'create posts',
            'view comments', 'edit comments', 'delete comments', 'create comments',
            'publish posts', 'unpublish posts', 'ban users', 'assign roles'
        ]; 
        $permissions = [];
        foreach ($permissionNames as $name) {
            $permissions[] = Permission::firstOrCreate(['name' => $name]);
        }
        $roleNames = [
            'User', 'Moderator', 'Editor', 'Author', 'Manager',
            'Support', 'Admin', 'Super Admin', 'Guest', 'Contributor'
        ];
    
        $roles = [];
        foreach ($roleNames as $name) {
            $role = Role::firstOrCreate(['name' => $name]);
            $randomPermissions = collect($permissions)->random(rand(1, 10));
            $role->syncPermissions($randomPermissions);
            $roles[] = $role;
        }
    
        $firstNames = ['John', 'Jane', 'Michael', 'Emily', 'David', 'Sarah', 'Robert', 'Jennifer', 'William', 'Lisa'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Miller', 'Davis', 'Garcia', 'Rodriguez', 'Wilson'];

        for ($i = 0; $i < 20; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $email = Str::lower($firstName).'.'.Str::lower($lastName).($i > 0 ? $i : '').'@example.com';

            $userId = DB::table('users')->insertGetId([
                'name' => "$firstName $lastName",
                'email' => $email,
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Assign 1-3 random roles to each user
            $randomRoles = collect($roles)->random(rand(1, 3));
            foreach ($randomRoles as $role) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $role->id,
                    'model_type' => 'App\\Models\\User',
                    'model_id' => $userId,
                ]);
            }
        }

        // Admin user with all roles
        $adminId = DB::table('users')->insertGetId([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($roles as $role) {
            DB::table('model_has_roles')->insert([
                'role_id' => $role->id,
                'model_type' => 'App\\Models\\User',
                'model_id' => $adminId,
            ]);
        }
    }
}