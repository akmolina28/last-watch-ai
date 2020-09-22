<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetectionProfileWebRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detection_profile_web_request_cfg', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detection_profile_id')->references('id')->on('detection_profiles');
            $table->foreignId('web_request_config_id')->references('id')->on('web_request_configs');
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
        Schema::dropIfExists('detection_profile_web_request_cfg');
    }
}
