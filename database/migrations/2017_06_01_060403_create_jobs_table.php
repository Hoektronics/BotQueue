<?php

use App\Enums\JobStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');

            $table->string('status')->default(JobStatusEnum::QUEUED);
            $table->index('status');

            $table->integer('creator_id')->unsigned();
            $table->foreign('creator_id')->references('id')->on('users');

            $table->integer('worker_id')->unsigned();
            $table->string('worker_type');

            $table->integer('bot_id')->unsigned()->nullable();
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
        Schema::dropIfExists('jobs');
    }
}
