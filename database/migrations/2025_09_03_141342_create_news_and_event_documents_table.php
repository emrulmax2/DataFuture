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
        Schema::create('news_and_event_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('news_and_event_id');
            $table->foreign('news_and_event_id')->references('id')->on('news_and_events')->onDelete('cascade');
            $table->tinyInteger('hard_copy_check')->nullable();
            $table->string('doc_type', 145)->nullable();
            $table->string('disk_type', 145)->nullable();
            $table->text('path');
            $table->string('display_file_name');
            $table->string('current_file_name');
            

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_and_event_documents');
    }
};
