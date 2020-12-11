<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeepstackCallIdToDetectionEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detection_events', function (Blueprint $table) {
            $table->foreignId('deepstack_call_id')->nullable()
                ->references('id')->on('deepstack_calls');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detection_events', function (Blueprint $table) {
            $table->dropForeign('detection_events_deepstack_call_id_foreign');
            $table->dropColumn('deepstack_call_id');
        });
    }
}
