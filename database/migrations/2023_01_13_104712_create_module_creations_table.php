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
        Schema::create('module_creations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_term_id');
            $table->unsignedBigInteger('course_module_id');
            $table->unsignedBigInteger('module_level_id')->nullable();
            $table->string('module_name', 191);
            $table->string('code', 45);
            $table->enum('status', ['core', 'specialist', 'optional'])->nullable();
            $table->string('credit_value', 45);
            $table->string('unit_value', 45);
            $table->string('moodle_enrollment_key', 45)->nullable();
            $table->enum('class_type', ['Theory','Practical','Tutorial','Seminar'])->nullable();
            $table->date('submission_date')->nullable();

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('instance_term_id')->references('id')->on('instance_terms');
            $table->foreign('course_module_id')->references('id')->on('course_modules');
            $table->foreign('module_level_id')->references('id')->on('module_levels');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('module_creations');
    }
};
