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
        Schema::create('student_address_update_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('student_task_id')->nullable();
            $table->foreign('student_task_id')->references('id')->on('student_tasks')->onDelete('set null');
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city', 191);
            $table->string('state', 191)->nullable();
            $table->string('postal_code', 191);
            $table->string('country', 191)->nullable();
            $table->string('latitude', 191)->nullable();
            $table->string('longitude', 191)->nullable();

            $table->enum('status', ['Pending', 'In Progress', 'Completed', 'Canceled'])->default('Pending');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('students')->onDelete('set null');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('students')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_address_update_requests');
    }
};
