<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInternalQueueJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('internal_queue_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue');
            $table->longText('payload');
            $table->tinyInteger('attempts')->unsigned();
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');

            $table->index(['queue', 'reserved_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('internal_queue_jobs');
    }
}
