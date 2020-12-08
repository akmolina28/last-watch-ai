<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCascadeToPatternMatchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pattern_match', function (Blueprint $table) {
            $table->dropForeign('pattern_match_dtcevt_id');

            $table->foreign('detection_event_id', 'pattern_match_dtcevt_id')
                ->references('id')
                ->on('detection_events')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pattern_match', function (Blueprint $table) {
            $table->dropForeign('pattern_match_dtcevt_id');

            $table->foreign('detection_event_id', 'pattern_match_dtcevt_id')
                ->references('id')
                ->on('detection_events');
        });
    }
}
