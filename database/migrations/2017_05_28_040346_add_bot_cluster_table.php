<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBotClusterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bot_cluster', function (Blueprint $table) {
            $table->integer('bot_id')->unsigned();
            $table->integer('cluster_id')->unsigned();
            $table->foreign('bot_id')->references('id')->on('bots');
            $table->foreign('cluster_id')->references('id')->on('clusters');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bot_cluster');
    }
}
