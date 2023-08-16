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
        Schema::create('task_list_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_list_id');
            $table->unsignedBigInteger('user_id');
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('user_id')->nullable()->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('task_list_id')->references('id')->on('task_lists')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('task_list_users', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['task_list_id']);
        });
        Schema::dropIfExists('task_list_users');
    }
};
