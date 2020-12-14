<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropDeepstackResponseFromDetectionEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detection_events', function (Blueprint $table) {
            $table->dropColumn('deepstack_response');
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
            $table->longText('deepstack_response')->nullable();
        });
    }
}
