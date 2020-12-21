<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsCustomPayloadToMqttPublishConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mqtt_publish_configs', function (Blueprint $table) {
            $table->boolean('is_custom_payload')->default(false);
            $table->longText('payload_json_temp');
        });

        Schema::table('mqtt_publish_configs', function (Blueprint $table) {
            DB::statement('UPDATE mqtt_publish_configs SET payload_json_temp = payload_json, is_custom_payload = 1');
            $table->dropColumn('payload_json');
        });

        Schema::table('mqtt_publish_configs', function (Blueprint $table) {
            $table->longText('payload_json')->nullable();
        });

        Schema::table('mqtt_publish_configs', function (Blueprint $table) {
            DB::statement('UPDATE mqtt_publish_configs SET payload_json = payload_json_temp');
            $table->dropColumn('payload_json_temp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mqtt_publish_configs', function (Blueprint $table) {
            $table->dropColumn('is_custom_payload');
            $table->longText('payload_json_temp');
        });

        Schema::table('mqtt_publish_configs', function (Blueprint $table) {
            DB::statement('UPDATE mqtt_publish_configs SET payload_json_temp = payload_json');
            $table->dropColumn('payload_json');
        });

        Schema::table('mqtt_publish_configs', function (Blueprint $table) {
            $table->longText('payload_json');
        });

        Schema::table('mqtt_publish_configs', function (Blueprint $table) {
            DB::statement('UPDATE mqtt_publish_configs SET payload_json = payload_json_temp');
            $table->dropColumn('payload_json_temp');
        });
    }
}
