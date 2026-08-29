<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryScanColumnsToOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_details', function (Blueprint $table) {
            if (!Schema::hasColumn('order_details', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('delivery_status');
            }
            if (!Schema::hasColumn('order_details', 'delivered_by')) {
                $table->unsignedBigInteger('delivered_by')->nullable()->after('delivered_at');
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
        Schema::table('order_details', function (Blueprint $table) {
            if (Schema::hasColumn('order_details', 'delivered_by')) {
                $table->dropColumn('delivered_by');
            }
            if (Schema::hasColumn('order_details', 'delivered_at')) {
                $table->dropColumn('delivered_at');
            }
        });
    }
}
