<?php

use App\Enums\HostRequestStatusEnum;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHostRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('host_requests', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->timestamps();
            $table->timestamp('expires_at')->nullable();

            $table->string('local_ip')->nullable();
            $table->string('remote_ip')->nullable();
            $table->string('hostname')->nullable();

            $table->string('status')->default(HostRequestStatusEnum::REQUESTED);

            $table->integer('claimer_id')->unsigned()->nullable();
            $table->foreign('claimer_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('host_requests');
    }
}
