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
        Schema::create('student_address_update_request_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_address_update_request_id')->nullable();
            $table->foreign('student_address_update_request_id', 'fk_saur_id')->references('id')->on('student_address_update_requests')->onDelete('set null');
            $table->tinyInteger('hard_copy_check')->nullable();
            $table->string('doc_type', 145)->nullable();
            $table->string('disk_type', 145)->nullable();
            $table->text('path');
            $table->string('display_file_name');
            $table->string('current_file_name');
            

            $table->enum('status', ['Pending', 'Approved', 'Cancelled'])->default('Pending');
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
        Schema::dropIfExists('student_address_update_request_documents');
    }
};
