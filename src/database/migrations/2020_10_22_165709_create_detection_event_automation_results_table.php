<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetectionEventAutomationResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detection_event_automation_results', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('detection_event_id')->references('id')->on('detection_events');
            $table->foreignId('automation_config_id')->references('id')->on('automation_configs');

            $table->boolean('is_error')->default(false);
            $table->longText('response_text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detection_event_automation_results');
    }
}
