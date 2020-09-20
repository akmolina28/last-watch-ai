<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetectionProfileTelegramConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detection_profile_telegram_config', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detection_profile_id')->references('id')->on('detection_profiles');
            $table->foreignId('telegram_config_id')->references('id')->on('telegram_configs');
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
        Schema::dropIfExists('detection_profile_telegram_config');
    }
}
