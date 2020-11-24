<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsNegativeToDetectionProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detection_profiles', function (Blueprint $table) {
            $table->boolean('is_negative')->default(false);
        });

        DB::statement('UPDATE detection_profiles SET is_negative = false');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detection_profiles', function (Blueprint $table) {
            $table->dropColumn('is_negative');
        });
    }
}
