<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetectionProfileAiPredictionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ai_prediction_detection_profile', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detection_profile_id')->references('id')->on('detection_profiles');
            $table->foreignId('ai_prediction_id')->references('id')->on('ai_predictions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detection_profile_ai_predictions');
    }
}
