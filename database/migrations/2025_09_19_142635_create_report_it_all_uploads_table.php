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
        Schema::create('report_it_all_uploads', function (Blueprint $table) {
            $table->id();
            //file_upload_system
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_extension')->nullable();
            $table->enum('uploaded_to', ['s3', 'local'])->default('s3');
            $table->unsignedDouble('file_size')->nullable();
            $table->string('original_name')->nullable();
            $table->enum('file_type', ['image', 'video', 'document', 'other'])->default('other');
            $table->string('mime_type')->nullable();
            $table->foreignId('report_it_all_id')->constrained('report_it_alls')->onDelete('cascade');
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_it_all_uploads');
    }
};
