<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIgnoreZonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ignore_zones', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('detection_profile_id')->references('id')->on('detection_profiles');
            $table->string('object_class')->nullable();
            $table->integer('x_min');
            $table->integer('x_max');
            $table->integer('y_min');
            $table->integer('y_max');
            $table->datetime('expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ignore_zones');
    }
}
