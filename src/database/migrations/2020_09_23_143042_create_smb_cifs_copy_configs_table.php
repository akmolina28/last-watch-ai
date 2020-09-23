<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmbCifsCopyConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('smb_cifs_copy_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('servicename'); // e.g. //192.168.1.123/config
            $table->string('user'); // e.g. hassio
            $table->string('password'); // e.g. hassio
            $table->string('remote_dest'); // e.g. /www/images/ai_alerts
            $table->boolean('overwrite')->default(false);
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
        Schema::dropIfExists('smb_cifs_copy_configs');
    }
}
