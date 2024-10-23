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
        Schema::create('student_awards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('student_course_relation_id');
            $table->date('date_of_award')->nullable();
            $table->unsignedBigInteger('qual_award_result_id')->nullable();
            $table->enum('certificate_requested', ['Yes', 'No'])->nullable()->default('No');
            $table->date('date_of_certificate_requested')->nullable();
            $table->unsignedBigInteger('certificate_requested_by')->nullable();
            $table->enum('certificate_received', ['Yes', 'No'])->nullable()->default('No');
            $table->date('date_of_certificate_received')->nullable();
            $table->enum('certificate_released', ['Yes', 'No'])->nullable()->default('No');
            $table->date('date_of_certificate_released')->nullable();
            $table->unsignedBigInteger('certificate_released_by')->nullable();

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_awards');
    }
};
