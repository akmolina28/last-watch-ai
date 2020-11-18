<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsPostToWebRequestConfigs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('web_request_configs', function (Blueprint $table) {
            $table->boolean('is_post')->default(false);
            $table->longText('body_json')->nullable();
            $table->longText('headers_json')->nullable();
        });

        DB::statement('UPDATE web_request_configs SET is_post = false');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('web_request_configs', function (Blueprint $table) {
            $table->dropColumn('is_post');
            $table->dropColumn('body_json');
            $table->dropColumn('headers_json');
        });
    }
}
