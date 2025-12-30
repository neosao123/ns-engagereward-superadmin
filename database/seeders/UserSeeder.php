<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Superuser
        User::updateOrCreate(
            ['id' => 1],
            [
                'username'   => 'superuser',
                'password'   => Hash::make('password@123'),
                'first_name' => 'Super',
                'last_name'  => 'User',
                'email'      => 'superuser@engagereward.com',
                'phone'      => '+919800980001',
                'phone_country'=>'in',
                'is_active'  => 1,
                'role_id'    => 1,
                'is_block'   => 0,
            ]
        );

        // Admin user
        User::updateOrCreate(
            ['id' => 2],
            [
                'username'   => 'adminuser',
                'password'   => Hash::make('password@123'),
                'first_name' => 'Admin',
                'last_name'  => 'User',
                'email'      => 'admin@engagereward.com', 
                'phone'      => '+919800980002', 
                'phone_country'=>'in',
                'is_active'  => 1,
                'role_id'    => 2,
                'is_block'   => 0,
            ]
        );
    }
}
