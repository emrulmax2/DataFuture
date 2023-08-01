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
        Schema::create('task_lists', function (Blueprint $table) {
            $table->id();
            $table->index('process_list_id');
            $table->bigInteger('process_list_id')->unsigned();
            $table->string('name',191);
            $table->string('short_description',191)->nullable();
            $table->enum('interview', ['Yes', 'No'])->default('No');
            $table->enum('upload', ['Yes', 'No'])->default('No');
            $table->enum('external_link', ['0', '1'])->default('0');
            $table->text('external_link_ref')->nullable();
            $table->enum('status', ['Yes', 'No'])->default('No')->nullable();
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('process_list_id')->references('id')->on('process_lists')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('task_lists', function (Blueprint $table) {

            $table->dropForeign(['process_list_id']);
            
        });
        Schema::dropIfExists('task_lists');
    }
};
