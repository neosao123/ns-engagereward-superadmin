<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermissionGroupsTableSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        DB::table('permission_groups')->insertOrIgnore([
            [
                'id' => 1,
                'group_name' => 'Role',
                'slug' => 'role',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 2,
                'group_name' => 'User',
                'slug' => 'user',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 3,
                'group_name' => 'Welcome',
                'slug' => 'welcome',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 4,
                'group_name' => 'Dashboard',
                'slug' => 'dashboard',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 5,
                'group_name' => 'PermissionGroup',
                'slug' => 'permissiongroup',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 6,
                'group_name' => 'Permissions',
                'slug' => 'permissions',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 7,
                'group_name' => 'Social Platform',
                'slug' => 'social-platform',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 8,
                'group_name' => 'Company',
                'slug' => 'company',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 9,
                'group_name' => 'Setting',
                'slug' => 'setting',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 10,
                'group_name' => 'Subscription',
                'slug' => 'subscription',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ]);
    }
}
