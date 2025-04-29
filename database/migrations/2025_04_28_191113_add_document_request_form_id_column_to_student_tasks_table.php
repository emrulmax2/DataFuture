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
        Schema::table('student_tasks', function (Blueprint $table) {
            $table->foreignId('student_document_request_form_id')
                ->after('task_status_id')
                ->nullable()
                ->after('task_status_id')
                ->constrained('student_document_request_forms')
                ->nullOnDelete()
                ->comment('Foreign key to the student_document_request_forms table');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_tasks', function (Blueprint $table) {
            $table->dropForeign(['student_document_request_form_id']);
            $table->dropColumn('student_document_request_form_id');
        });
    }
};
