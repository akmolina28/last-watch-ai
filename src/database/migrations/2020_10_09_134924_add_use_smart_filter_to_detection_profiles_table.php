<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUseSmartFilterToDetectionProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detection_profiles', function (Blueprint $table) {
            $table->boolean('use_smart_filter')->default(false);
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
            $table->dropColumn('use_smart_filter');
        });
    }
}
