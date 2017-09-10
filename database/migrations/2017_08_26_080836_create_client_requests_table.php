<?php

use App\Enums\ClientRequestStatusEnum;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->timestamp('expires_at');

            $table->string('local_ip')->nullable();
            $table->string('remote_ip')->nullable();
            $table->string('hostname')->nullable();

            $table->string('status')->default(ClientRequestStatusEnum::Requested);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_requests');
    }
}
