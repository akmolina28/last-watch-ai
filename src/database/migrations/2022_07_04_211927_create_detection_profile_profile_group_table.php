<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetectionProfileProfileGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detection_profile_profile_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detection_profile_id')->references('id')->on('detection_profiles');
            $table->foreignId('profile_group_id')->references('id')->on('profile_groups');
            $table->softDeletes();
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
        Schema::dropIfExists('detection_profile_profile_group');
    }
}
