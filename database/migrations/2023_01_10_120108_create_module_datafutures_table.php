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
        Schema::create('module_datafutures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_module_id');

            $table->string('field_name', 191)->nullable();
            $table->enum('field_type', ['date', 'text', 'number'])->nullable();
            $table->string('field_value', 191)->nullable();
            $table->text('field_desc')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('course_module_id')->references('id')->on('course_modules');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('module_datafutures');
    }
};
