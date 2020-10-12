<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsSmartFilteredToAiPredictionDetectionProfileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ai_prediction_detection_profile', function (Blueprint $table) {
            $table->boolean('is_smart_filtered')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ai_prediction_detection_profile', function (Blueprint $table) {
            $table->dropColumn('is_smart_filtered');
        });
    }
}
