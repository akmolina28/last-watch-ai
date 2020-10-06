<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNullableToDetectionEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detection_events', function (Blueprint $table) {
            $table->dropColumn('occurred_at');
        });

        Schema::table('detection_events', function (Blueprint $table) {
            $table->timestamp('occurred_at')->nullable();
        });

        DB::statement('UPDATE detection_events SET occurred_at = created_at');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
