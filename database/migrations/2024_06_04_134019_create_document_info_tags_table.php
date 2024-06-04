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
        Schema::create('document_info_tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_info_id');
            $table->unsignedBigInteger('document_tag_id');

            $table->foreign('document_info_id')->references('id')->on('document_infos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('document_tag_id')->references('id')->on('document_tags')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_info_tags');
    }
};
