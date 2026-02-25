<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionTableSeeder extends Seeder
{
    public function run()
    {
        // Clear Spatie Permission Cache
        if (app()->bound(\Spatie\Permission\PermissionRegistrar::class)) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }

        $permissions = [
            ['id' => 1, 'name' => 'Role.Create-Update', 'guard_name' => 'admin', 'group_id' => 1],
            ['id' => 2, 'name' => 'Role.List', 'guard_name' => 'admin', 'group_id' => 1],
            ['id' => 3, 'name' => 'Role.Delete', 'guard_name' => 'admin', 'group_id' => 1],
            ['id' => 4, 'name' => 'User.List', 'guard_name' => 'admin', 'group_id' => 2],
            ['id' => 5, 'name' => 'User.Create', 'guard_name' => 'admin', 'group_id' => 2],
            ['id' => 6, 'name' => 'User.Edit', 'guard_name' => 'admin', 'group_id' => 2],
            ['id' => 7, 'name' => 'User.Delete', 'guard_name' => 'admin', 'group_id' => 2],
            ['id' => 8, 'name' => 'User.Export', 'guard_name' => 'admin', 'group_id' => 2],
            ['id' => 9, 'name' => 'User.Import', 'guard_name' => 'admin', 'group_id' => 2],
            ['id' => 10, 'name' => 'User.Permissions', 'guard_name' => 'admin', 'group_id' => 2],
            ['id' => 11, 'name' => 'User.Block', 'guard_name' => 'admin', 'group_id' => 2],
            ['id' => 12, 'name' => 'User.View', 'guard_name' => 'admin', 'group_id' => 2],
            ['id' => 13, 'name' => 'Welcome.View', 'guard_name' => 'admin', 'group_id' => 3],
            ['id' => 14, 'name' => 'Dashboard.View', 'guard_name' => 'admin', 'group_id' => 4],
            ['id' => 15, 'name' => 'PermissionGroup.List', 'guard_name' => 'admin', 'group_id' => 5],
            ['id' => 16, 'name' => 'PermissionGroup.Create', 'guard_name' => 'admin', 'group_id' => 5],
            ['id' => 17, 'name' => 'Permissions.List', 'guard_name' => 'admin', 'group_id' => 6],
            ['id' => 18, 'name' => 'Permissions.Create', 'guard_name' => 'admin', 'group_id' => 6],
            ['id' => 19, 'name' => 'Role.Edit', 'guard_name' => 'admin', 'group_id' => 1],
            ['id' => 20, 'name' => 'Social Platform.List', 'guard_name' => 'admin', 'group_id' => 7],
            ['id' => 21, 'name' => 'Social Platform.Create', 'guard_name' => 'admin', 'group_id' => 7],
            ['id' => 22, 'name' => 'Social Platform.Edit', 'guard_name' => 'admin', 'group_id' => 7],
            ['id' => 23, 'name' => 'Social Platform.Delete', 'guard_name' => 'admin', 'group_id' => 7],
            ['id' => 24, 'name' => 'Social Platform.View', 'guard_name' => 'admin', 'group_id' => 7],
            ['id' => 25, 'name' => 'Company.List', 'guard_name' => 'admin', 'group_id' => 8],
            ['id' => 26, 'name' => 'Company.Create', 'guard_name' => 'admin', 'group_id' => 8],
            ['id' => 27, 'name' => 'Company.Edit', 'guard_name' => 'admin', 'group_id' => 8],
            ['id' => 28, 'name' => 'Company.Delete', 'guard_name' => 'admin', 'group_id' => 8],
            ['id' => 29, 'name' => 'Company.View', 'guard_name' => 'admin', 'group_id' => 8],
            ['id' => 30, 'name' => 'Company.Export', 'guard_name' => 'admin', 'group_id' => 8],
            ['id' => 31, 'name' => 'Company.Import', 'guard_name' => 'admin', 'group_id' => 8],
            ['id' => 32, 'name' => 'Setting.List', 'guard_name' => 'admin', 'group_id' => 9],
            ['id' => 33, 'name' => 'Setting.Create', 'guard_name' => 'admin', 'group_id' => 9],
            ['id' => 34, 'name' => 'Setting.Edit', 'guard_name' => 'admin', 'group_id' => 9],
            ['id' => 35, 'name' => 'Setting.Delete', 'guard_name' => 'admin', 'group_id' => 9],
            ['id' => 36, 'name' => 'Setting.View', 'guard_name' => 'admin', 'group_id' => 9],
            ['id' => 37, 'name' => 'Company.Status-Change', 'guard_name' => 'admin', 'group_id' => 8],
            ['id' => 38, 'name' => 'Subscription.List', 'guard_name' => 'admin', 'group_id' => 10],
            ['id' => 39, 'name' => 'Subscription.Create', 'guard_name' => 'admin', 'group_id' => 10],
            ['id' => 40, 'name' => 'Subscription.Edit', 'guard_name' => 'admin', 'group_id' => 10],
            ['id' => 41, 'name' => 'Subscription.Delete', 'guard_name' => 'admin', 'group_id' => 10],
            ['id' => 42, 'name' => 'Subscription.View', 'guard_name' => 'admin', 'group_id' => 10],
            ['id' => 44, 'name' => 'PaymentSetting.Create', 'guard_name' => 'admin', 'group_id' => 11],
            ['id' => 45, 'name' => 'MetaSetting.Edit', 'guard_name' => 'admin', 'group_id' => 12],
            ['id' => 46, 'name' => 'AppSetting.Edit', 'guard_name' => 'admin', 'group_id' => 13],
            ['id' => 47, 'name' => 'InstagramSetting.Edit', 'guard_name' => 'admin', 'group_id' => 14],
            ['id' => 48, 'name' => 'Role.Role-Permission', 'guard_name' => 'admin', 'group_id' => 1],
        ];

        try {
            DB::beginTransaction();

            foreach ($permissions as $perm) {
                $permissionName = $perm['name'];

                // Check existence via DB directly
                $existing = DB::table('permissions')
                    ->where('name', $permissionName)
                    ->where('guard_name', 'admin')
                    ->first();

                if (!$existing) {
                    $pId = DB::table('permissions')->insertGetId([
                        'name' => $permissionName,
                        'guard_name' => 'admin',
                        'group_id' => $perm['group_id'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                    $permissionId = $pId;
                } else {
                    $permissionId = $existing->id;
                    DB::table('permissions')
                        ->where('id', $permissionId)
                        ->update(['group_id' => $perm['group_id'], 'updated_at' => Carbon::now()]);
                }

                // Assign to Super Admin (Role 1) and Admin (Role 2)
                foreach ([1, 2] as $roleId) {
                    $roleExists = DB::table('roles')->where('id', $roleId)->exists();
                    if ($roleExists) {
                        $pivotExists = DB::table('role_has_permissions')
                            ->where('permission_id', $permissionId)
                            ->where('role_id', $roleId)
                            ->exists();

                        if (!$pivotExists) {
                            DB::table('role_has_permissions')->insert([
                                'permission_id' => $permissionId,
                                'role_id' => $roleId,
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            echo "Permissions seeded successfully.\n";
        } catch (\Exception $exception) {
            DB::rollBack();
            echo "Seeding failed: " . $exception->getMessage() . "\n";
        }
    }
}
