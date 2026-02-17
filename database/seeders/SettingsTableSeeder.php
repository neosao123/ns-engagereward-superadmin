<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $settings = [
            [
                'setting_name' => 'android', 
                'setting_value' => '1.0.0',
                'is_update_compulsory' => 0,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'setting_name' => 'ios',
                'setting_value' => '1.0.8',
                'is_update_compulsory' => 1,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('settings')->insert($settings);
    }
}
