<?php

use Illuminate\Database\Migrations\Migration;

class InsertAdminUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('users')->insert([
            [
                'name' => 'Adminstrator',
                'email' => 'admin',
                'password' => bcrypt(env('PASSWORD', 'password')),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('users')->truncate();
    }
}
