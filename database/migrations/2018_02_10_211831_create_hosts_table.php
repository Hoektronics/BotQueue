<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hosts', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->timestamp('seen_at')->nullable();

            $table->string('local_ip')->nullable();
            $table->string('remote_ip')->nullable();
            $table->string('name');

            $table->integer('owner_id')->unsigned();
            $table->foreign('owner_id')->references('id')->on('users');

            $table->string('token_id', 100);
            $table->foreign('token_id')->references('id')->on('oauth_access_tokens');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hosts');
    }
}
