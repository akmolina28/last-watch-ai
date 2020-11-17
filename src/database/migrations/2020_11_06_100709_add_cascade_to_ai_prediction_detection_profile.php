<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCascadeToAiPredictionDetectionProfile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ai_prediction_detection_profile', function (Blueprint $table) {
            $table->dropForeign('ai_prediction_detection_profile_ai_prediction_id_foreign');

            $table->foreign('ai_prediction_id', 'ai_prediction_detection_profile_ai_prediction_id_foreign')
                ->references('id')
                ->on('ai_predictions')
                ->cascadeOnDelete();
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
            $table->dropForeign('ai_prediction_detection_profile_ai_prediction_id_foreign');

            $table->foreign('ai_prediction_id', 'ai_prediction_detection_profile_ai_prediction_id_foreign')
                ->references('id')
                ->on('ai_predictions');
        });
    }
}
