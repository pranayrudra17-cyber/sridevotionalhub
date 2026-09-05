<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddExportOrdersPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permissions = array(
            'export_orders_pdf',
            'export_orders_excel',
        );

        foreach ($permissions as $name) {
            $permissionId = DB::table('permissions')->where('name', $name)->value('id');
            if (!$permissionId) {
                DB::table('permissions')->insert(array(
                    'name' => $name,
                    'section' => 'sale',
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
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
        $permissionIds = DB::table('permissions')
            ->whereIn('name', array('export_orders_pdf', 'export_orders_excel'))
            ->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('role_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('model_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('permissions')->whereIn('id', $permissionIds)->delete();
        }

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
}
