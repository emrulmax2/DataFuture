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
        Schema::create('attendance_excuse_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_excuse_id');
            $table->tinyInteger('hard_copy_check')->default(0)->nullable();
            $table->string('doc_type', 145)->nullable();
            $table->string('disk_type', 145)->nullable();
            $table->text('path')->nullable();
            $table->text('display_file_name');
            $table->text('current_file_name');

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('attendance_excuse_id')->references('id')->on('attendance_excuses')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_excuse_documents');
    }
};
