<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSmartFilterPrecisionToDetectionProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detection_profiles', function (Blueprint $table) {
            $table->decimal('smart_filter_precision')->default('0');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detection_profiles', function (Blueprint $table) {
            $table->dropColumn('smart_filter_precision');
        });
    }
}
