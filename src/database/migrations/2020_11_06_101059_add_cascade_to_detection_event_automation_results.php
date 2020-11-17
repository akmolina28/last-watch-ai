<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCascadeToDetectionEventAutomationResults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detection_event_automation_results', function (Blueprint $table) {
            $table->dropForeign('detection_event_automation_results_detection_event_id_foreign');

            $table->foreign('detection_event_id', 'detection_event_automation_results_detection_event_id_foreign')
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
        Schema::table('detection_event_automation_results', function (Blueprint $table) {
            $table->dropForeign('detection_event_automation_results_detection_event_id_foreign');

            $table->foreign('detection_event_id', 'detection_event_automation_results_detection_event_id_foreign')
                ->references('id')
                ->on('detection_events');
        });
    }
}
