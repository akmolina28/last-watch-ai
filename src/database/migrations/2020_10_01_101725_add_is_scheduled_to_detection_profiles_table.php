<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsScheduledToDetectionProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detection_profiles', function (Blueprint $table) {
            $table->boolean('is_scheduled')->default(false);
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();

            DB::statement('UPDATE detection_profiles SET is_enabled = false');
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
            $table->dropColumn('is_scheduled');
            $table->dropColumn('start_time');
            $table->dropColumn('end_time');
        });
    }
}
