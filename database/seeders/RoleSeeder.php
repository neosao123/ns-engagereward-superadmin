<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
	
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		DB::table('roles')->insertOrIgnore([
            [
                'id' => 1,
                'name' => 'Super Admin',
                'guard_name' => 'admin',
                'created_at' => '2025-06-20 11:27:48',
                'updated_at' => '2025-06-20 11:27:48'
            ],
			[
                'id' => 2,
                'name' => 'Admin',
                'guard_name' => 'admin',
                'created_at' => '2025-06-20 11:27:48',
                'updated_at' => '2025-06-20 11:27:48'
            ]
	    ]);
	}
}