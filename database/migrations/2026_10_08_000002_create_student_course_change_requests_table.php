<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Course Change Request Form, the first of the "Do it online" forms to move
 * off Google Forms and into the portal. The course the student is on is kept
 * as a snapshot as well as a foreign key: a request is a record of what they
 * asked for on the day, and their enrolment can move underneath it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_course_change_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('forms_table_id')->nullable();

            $table->unsignedBigInteger('current_course_creation_id')->nullable();
            $table->string('current_course_name', 191)->nullable();
            $table->date('current_course_start_date')->nullable();

            $table->unsignedBigInteger('proposed_course_id')->nullable();
            $table->string('proposed_course_name', 191)->nullable();
            $table->date('proposed_start_date')->nullable();

            $table->text('reason')->nullable();
            $table->boolean('declaration_accepted')->default(0);
            $table->string('declaration_name', 191)->nullable();

            $table->enum('status', ['Pending', 'In Progress', 'Completed', 'Canceled'])->default('Pending');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('student_id');
            $table->index('status');
        });

        Schema::create('student_course_change_request_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_course_change_request_id');
            $table->string('doc_type', 145)->nullable();
            $table->string('disk_type', 145)->nullable();
            $table->text('path')->nullable();
            $table->string('display_file_name', 191)->nullable();
            $table->string('current_file_name', 191)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('student_course_change_request_id', 'sccrd_request_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_course_change_request_documents');
        Schema::dropIfExists('student_course_change_requests');
    }
};
