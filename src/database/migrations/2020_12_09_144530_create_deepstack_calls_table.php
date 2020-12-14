<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeepstackCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deepstack_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detection_event_id')
                ->references('id')->on('detection_events')
                ->cascadeOnDelete();
            $table->timestamps();
            $table->string('input_file');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->longText('response_json')->nullable();
            $table->boolean('is_error')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deepstack_calls');
    }
}
