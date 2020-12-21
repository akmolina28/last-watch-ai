<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMqttPublishConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mqtt_publish_configs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('server');
            $table->string('port');
            $table->string('topic');
            $table->string('client_id')->nullable();
            $table->integer('qos');
            $table->boolean('is_anonymous')->default(true);
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->longText('payload_json');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mqtt_publish_configs');
    }
}
