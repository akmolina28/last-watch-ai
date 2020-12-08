<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCascadeToAiPredictions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ai_predictions', function (Blueprint $table) {
            $table->dropForeign('ai_predictions_detection_event_id_foreign');

            $table->foreign('detection_event_id', 'ai_predictions_detection_event_id_foreign')
                ->references('id')
                ->on('detection_events')
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
        Schema::table('ai_predictions', function (Blueprint $table) {
            $table->dropForeign('ai_predictions_detection_event_id_foreign');

            $table->foreign('detection_event_id', 'ai_predictions_detection_event_id_foreign')
                ->references('id')
                ->on('detection_events');
        });
    }
}
