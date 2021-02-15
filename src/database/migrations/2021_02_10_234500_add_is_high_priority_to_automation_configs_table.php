<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsHighPriorityToAutomationConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('automation_configs', function (Blueprint $table) {
            $table->boolean('is_high_priority')->default(false);
        });

        DB::statement('UPDATE automation_configs SET is_high_priority = 0');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('automation_configs', function (Blueprint $table) {
            Schema::table('automation_configs', function (Blueprint $table) {
                $table->dropColumn('is_high_priority');
            });
        });
    }
}
