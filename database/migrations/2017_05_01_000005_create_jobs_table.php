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
            $table->uuid('id')->primary();
            $table->string('name');

            $table->string('status')->default(JobStatusEnum::QUEUED);
            $table->index('status');

            $table->uuid('creator_id');
            $table->foreign('creator_id')->references('id')->on('users');

            $table->uuid('worker_id');
            $table->string('worker_type');

            $table->uuid('bot_id')->nullable();
            $table->foreign('bot_id')->references('id')->on('bots');

            $table->timestamps();

            $table->uuid('file_id')->nullable();
            $table->foreign('file_id')->references('id')->on('files');

            $table->float('progress')->default(0);
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
