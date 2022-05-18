<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddIsRelevantToAiPredictionDetectionProfileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ai_prediction_detection_profile', function (Blueprint $table) {
            $table->boolean('is_relevant')->default(true);
        });
        DB::statement(
            'update ai_prediction_detection_profile 
             set is_relevant = ! (is_masked OR is_smart_filtered OR is_size_filtered)'
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ai_prediction_detection_profile', function (Blueprint $table) {
            $table->dropColumn('is_relevant');
        });
    }
}
