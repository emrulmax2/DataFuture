<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('venue_ip_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('venue_id');
            $table->string('ip', 50);

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('venue_id')->references('id')->on('venues')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('venue_ip_addresses');
    }
};
