<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_module_instance_datafutures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('student_course_relation_id');
            $table->unsignedBigInteger('student_stuload_information_id');
            $table->unsignedBigInteger('instance_term_id')->nullable();
            $table->unsignedBigInteger('course_module_id')->nullable();
            
            $table->unsignedBigInteger('MODULEOUTCOME')->nullable();
            $table->unsignedBigInteger('MODULERESULT')->nullable();

            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_module_instance_datafutures');
    }
};
