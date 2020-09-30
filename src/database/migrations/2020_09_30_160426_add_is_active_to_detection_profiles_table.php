<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsActiveToDetectionProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detection_profiles', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });

        DB::statement('UPDATE detection_profiles SET is_active = true');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detection_profiles', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
}
