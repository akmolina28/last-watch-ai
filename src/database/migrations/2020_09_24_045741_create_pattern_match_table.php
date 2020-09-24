<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatternMatchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pattern_match', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('detection_event_id')->unsigned();
            $table->foreign('detection_event_id', 'pattern_match_dtcevt_id')->references('id')->on('detection_events');
            $table->bigInteger('detection_profile_id')->unsigned();
            $table->foreign('detection_profile_id', 'pattern_match_dtcprf_id')->references('id')->on('detection_profiles');
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
        Schema::dropIfExists('pattern_match');
    }
}
