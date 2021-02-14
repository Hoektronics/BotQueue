<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->timestamps();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('stopped_at')->nullable();

            $table->string('type');
            $table->json('data');
            $table->string('status');

            $table->uuid('job_id');
            $table->foreign('job_id')->references('id')->on('jobs');

            $table->uuid('depends_on')->nullable();
            $table->foreign('depends_on')->references('id')->on('tasks');

            $table->uuid('input_file_id')->nullable();
            $table->foreign('input_file_id')->references('id')->on('files');

            $table->uuid('output_file_id')->nullable();
            $table->foreign('output_file_id')->references('id')->on('files');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
