<?php

namespace Database\Seeders;

use App\Domains\Core\Permission\Models\Permission;
use App\Domains\Core\Role\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('role_has_permission')->delete();
        $roles = Role::all();

        
        foreach ($roles as $role) {
            switch ($role->id) {
                case 1:
                    $allPermissions = Permission::get();
                    $role->permissions()->sync($allPermissions);
                    break;

                case 2:
                    // Master permission
                    $masterPermissions = [
                        // 'project_access', 'project_view', 
                        // 'daily_activity_log_access', 'daily_activity_log_create', 'daily_activity_log_edit', 'daily_activity_log_delete', 'daily_activity_log_view'
                    ];
                    $masterPermissionRecord = Permission::whereIn('name', $masterPermissions)->get();
                    $role->permissions()->sync($masterPermissionRecord);
                    break;
                default:
                    break;
            }
        }
    }
}
