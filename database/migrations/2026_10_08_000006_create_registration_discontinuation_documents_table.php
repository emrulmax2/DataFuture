<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Files attached to a registration discontinuation request. Mirrors the course
 * change documents table, including `storage_key` — which disk a file ended up
 * on is not something to rebuild by convention.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_registration_discontinuation_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id');
            $table->string('doc_type', 145)->nullable();
            $table->string('disk_type', 145)->nullable();
            $table->text('path')->nullable();
            $table->string('storage_key', 512)->nullable();
            $table->string('display_file_name', 191)->nullable();
            $table->string('current_file_name', 191)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('request_id', 'srdd_request_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_registration_discontinuation_documents');
    }
};
