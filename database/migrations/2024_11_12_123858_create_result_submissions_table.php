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
        Schema::create('result_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_creation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_plan_id')->nullable()->constrained('assessment_plans')->nullOnDelete();
            $table->foreignId('grade_id')->nullable()->constrained('grades')->nullOnDelete();
            $table->foreignId('module_creation_id')->nullable()->constrained('module_creations')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->string('module_code')->nullable();
            $table->enum('upload_user_type', ['personal_tutor', 'staff'])->default('personal_tutor');
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
        Schema::dropIfExists('result_submissions');
    }
};
