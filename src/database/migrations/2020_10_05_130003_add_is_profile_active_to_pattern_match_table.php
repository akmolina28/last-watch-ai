<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsProfileActiveToPatternMatchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pattern_match', function (Blueprint $table) {
            $table->boolean('is_profile_active')->default(true);
        });

        DB::statement('UPDATE pattern_match SET is_profile_active = true');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pattern_match', function (Blueprint $table) {
            $table->dropColumn('is_profile_active');
        });
    }
}
