<?php

use App\Enums\BotStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bots', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('creator_id');
            $table->foreign('creator_id')->references('id')->on('users');

            $table->string('name');
            $table->string('type');

            $table->string('status')->default(BotStatusEnum::OFFLINE);
            $table->index('status');
            $table->longText('error_text')->nullable();

            $table->timestamp('seen_at')->nullable();
            $table->timestamps();

            $table->uuid('host_id')->nullable();
            $table->foreign('host_id')->references('id')->on('hosts');

            $table->uuid('cluster_id');
            $table->foreign('cluster_id')->references('id')->on('clusters');

            $table->string('driver')->nullable();

            $table->boolean('job_available')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bots');
    }
}
