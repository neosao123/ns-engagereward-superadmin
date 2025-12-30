<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SocialMediaAppsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        $socialMediaApps = [
            [
                'id' => 1,
                'app_name' => 'Instagram',
                'app_logo' => 'app-logo/app-logo-1758817178.png',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'app_name' => 'Facebook',
                'app_logo' => 'app-logo/app-logo-1758817639.png',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'app_name' => 'LinkedIn',
                'app_logo' => 'app-logo/app-logo-1756382938.jpg',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'app_name' => 'Snapchat',
                'app_logo' => 'app-logo/app-logo-1760273012.png',
                'is_active' => 0,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'id' => 5,
                'app_name' => 'X',
                'app_logo' => 'app-logo/app-logo-1758817291.png',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'id' => 6,
                'app_name' => 'TikTok',
                'app_logo' => 'app-logo/app-logo-1760273065.png',
                'is_active' => 0,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'id' => 7,
                'app_name' => 'Youtube',
                'app_logo' => 'app-logo/app-logo-1758817238.png',
                'is_active' => 0,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
        ];

        DB::table('social_media_apps')->insert($socialMediaApps);
    }
}