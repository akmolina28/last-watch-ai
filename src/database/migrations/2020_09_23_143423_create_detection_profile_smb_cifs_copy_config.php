<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetectionProfileSmbCifsCopyConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detection_profile_smb_cifs_copy_config', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('detection_profile_id')->unsigned();
            $table->foreign('detection_profile_id', 'dpscc_dtpf_id_foreign')->references('id')->on('detection_profiles');

            $table->bigInteger('smb_cifs_copy_config_id')->unsigned();
            $table->foreign('smb_cifs_copy_config_id', 'dpscc_scc_id_foreign')->references('id')->on('smb_cifs_copy_configs');

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
        Schema::dropIfExists('detection_profile_smb_cifs_copy_config');
    }
}
