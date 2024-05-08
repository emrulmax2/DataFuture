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
        Schema::create('document_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_folder_id');
            $table->string('doc_type', 45)->nullable();
            $table->string('disk_type', 45)->nullable();
            $table->text('path')->nullable();
            $table->string('display_file_name', 191)->nullable();
            $table->string('current_file_name', 191)->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->timestamp('reminder_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('document_folder_id')->references('id')->on('document_folders')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_infos');
    }
};
