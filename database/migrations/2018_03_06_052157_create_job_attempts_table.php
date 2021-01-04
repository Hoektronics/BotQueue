<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('job_id');
            $table->foreign('job_id')->references('id')->on('jobs');

            $table->uuid('bot_id');
            $table->foreign('bot_id')->references('id')->on('bots');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_attempts');
    }
}
