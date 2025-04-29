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
        Schema::create('student_document_request_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('term_declaration_id');
            $table->unsignedBigInteger('letter_set_id');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->enum('service_type', ['Same Day (cost £10.00)', '3 Working Days (Free)']);
            $table->enum('status', ['Pending','In Progress', 'Approved', 'Rejected'])->default('Pending');
            $table->enum('email_status', ['Pending','Sent','N/A'])->default('N/A');
            $table->boolean('student_consent')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('letter_set_id')->references('id')->on('letter_sets')->onDelete('cascade');
            $table->foreign('term_declaration_id')->references('id')->on('term_declarations')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_document_request_forms');
    }
};
