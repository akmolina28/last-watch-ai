<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetectionProfileFolderCopyConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detection_profile_folder_copy_config', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('detection_profile_id')->unsigned();
            $table->foreign('detection_profile_id', 'dtc_prf_id_foreign')->references('id')->on('detection_profiles');

            $table->bigInteger('folder_copy_config_id')->unsigned();
            $table->foreign('folder_copy_config_id', 'fdrcpy_id_foreign')->references('id')->on('folder_copy_configs');

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
        Schema::dropIfExists('detection_profile_folder_copy_config');
    }
}
