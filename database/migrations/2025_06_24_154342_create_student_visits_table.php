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
        Schema::create('student_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->enum('visit_type', ['non-academic', 'academic'])->default('non-academic');
            $table->date('visit_date')->nullable();
            $table->enum('visit_duration', ['30 minutes', '60 minutes', '90 minutes', '120 minutes'])->default('30 minutes');
            $table->text('visit_notes')->nullable();
            $table->integer('attendance_id')->nullable()->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('attendance_deleted_by')->nullable();
            $table->foreign('attendance_deleted_by')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_visits');
    }
};
