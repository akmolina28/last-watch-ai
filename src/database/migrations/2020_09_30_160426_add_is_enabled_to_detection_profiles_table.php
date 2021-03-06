<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsEnabledToDetectionProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detection_profiles', function (Blueprint $table) {
            $table->boolean('is_enabled')->default(true);
        });

        DB::statement('UPDATE detection_profiles SET is_enabled = true');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detection_profiles', function (Blueprint $table) {
            $table->dropColumn('is_enabled');
        });
    }
}
