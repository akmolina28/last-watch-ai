<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropImageFileColumnsFromDetectionEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detection_events', function (Blueprint $table) {
            $table->dropColumn('image_file_name');
            $table->dropColumn('image_dimensions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detection_events', function (Blueprint $table) {
            $table->string('image_file_name');
            $table->string('image_dimensions');
        });
    }
}
