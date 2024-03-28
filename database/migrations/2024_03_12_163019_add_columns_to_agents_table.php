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
        Schema::table('agents', function (Blueprint $table) {
            $table->unsignedBigInteger('title_id')->nullable()->after('id');
            $table->string('email', 145)->nullable()->after('last_name');
            $table->string('mobile', 145)->nullable()->after('email');
            $table->unsignedBigInteger('address_id')->nullable()->after('mobile');
            $table->unsignedBigInteger('photo')->nullable()->after('address_id');
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('set null')->onUpdate('set null');
            $table->foreign('title_id')->references('id')->on('titles')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agents', function (Blueprint $table) {

            $table->dropForeign(['address_id']);
            $table->dropForeign(['title_id']);
            $table->dropColumn(['title_id']);
            $table->dropColumn(['address_id']);
            $table->dropColumn(['email']);
            $table->dropColumn(['mobile']);

        });

    }
};
