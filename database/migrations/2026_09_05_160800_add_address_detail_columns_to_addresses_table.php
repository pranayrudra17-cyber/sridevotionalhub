<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddressDetailColumnsToAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('addresses', 'street_house_no')) {
                $table->string('street_house_no')->nullable()->after('address');
            }
            if (!Schema::hasColumn('addresses', 'nearby_landmark')) {
                $table->string('nearby_landmark')->nullable()->after('street_house_no');
            }
            if (!Schema::hasColumn('addresses', 'area_locality')) {
                $table->string('area_locality')->nullable()->after('nearby_landmark');
            }
            if (!Schema::hasColumn('addresses', 'additional_address_details')) {
                $table->text('additional_address_details')->nullable()->after('area_locality');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $columns = [
                'street_house_no',
                'nearby_landmark',
                'area_locality',
                'additional_address_details',
            ];

            $drop = [];
            foreach ($columns as $column) {
                if (Schema::hasColumn('addresses', $column)) {
                    $drop[] = $column;
                }
            }

            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
}
