<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$roles = Role::all();
foreach ($roles as $role) {
    echo "Role ID: {$role->id}, Name: {$role->name}, Guard: {$role->guard_name}\n";
    $permissions = $role->permissions()->pluck('name')->toArray();
    echo "Permissions: " . implode(', ', $permissions) . "\n\n";
}
