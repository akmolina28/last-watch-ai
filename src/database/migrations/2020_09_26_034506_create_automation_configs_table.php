<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automation_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detection_profile_id')->references('id')->on('detection_profiles');
            $table->morphs('automation_config', 'morph_index');
            $table->timestamps();
//
//            $table->unique(['name', 'automation_config_type'], 'automation_name_uniq');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('automation_configs');
    }
}
