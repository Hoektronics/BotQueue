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
            $table->increments('id');
            $table->timestamps();
            $table->timestamp('expires_at')->nullable();

            $table->string('local_ip')->nullable();
            $table->string('remote_ip')->nullable();
            $table->string('hostname')->nullable();

            $table->string('status')->default(HostRequestStatusEnum::Requested);
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
