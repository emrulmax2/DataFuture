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
        Schema::create('report_it_all_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_it_all_id')->constrained('report_it_alls')->onDelete('cascade');
            $table->unsignedBigInteger('report_it_all_upload_id')->nullable();
            $table->foreign('report_it_all_upload_id')->references('id')->on('report_it_all_uploads')->onDelete('set null');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_it_all_logs');
    }
};
