<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsProcessedToDetectionEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detection_events', function (Blueprint $table) {
            $table->boolean('is_processed')->default(false);
        });

        DB::statement('UPDATE detection_events SET is_processed = 1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detection_events', function (Blueprint $table) {
            $table->dropColumn('is_processed');
        });
    }
}
