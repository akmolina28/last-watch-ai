<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfileGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::dropIfExists('profile_groups');

        Schema::create('profile_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
            $table->softDeletes();

            $table->string('v_deleted_at')->virtualAs('IF(`deleted_at` IS NOT NULL, cast(deleted_at as char), "NULL")');
        });
        Schema::table('profile_groups', function (Blueprint $table) {
            $table->unique(['name', 'v_deleted_at']);
            $table->unique(['slug', 'v_deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profile_groups');
    }
}
