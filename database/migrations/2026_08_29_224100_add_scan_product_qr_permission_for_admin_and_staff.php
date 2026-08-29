<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddScanProductQrPermissionForAdminAndStaff extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permissionId = DB::table('permissions')->where('name', 'scan_product_qr')->value('id');
        if (!$permissionId) {
            $permissionId = DB::table('permissions')->insertGetId(array(
                'name' => 'scan_product_qr',
                'section' => 'sale',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ));
        }

        $roleIds = DB::table('roles')->pluck('id');
        foreach ($roleIds as $roleId) {
            $exists = DB::table('role_has_permissions')
                ->where('permission_id', $permissionId)
                ->where('role_id', $roleId)
                ->exists();
            if (!$exists) {
                DB::table('role_has_permissions')->insert(array(
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ));
            }
        }

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permissionId = DB::table('permissions')->where('name', 'scan_product_qr')->value('id');
        if ($permissionId) {
            DB::table('role_has_permissions')->where('permission_id', $permissionId)->delete();
            DB::table('model_has_permissions')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
}
