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
        Schema::create('document_info_reminder_employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_info_reminder_id');
            $table->unsignedBigInteger('employee_id');

            $table->foreign('document_info_reminder_id', 'dirid_fk_dire')->references('id')->on('document_info_reminders')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_info_reminder_employees');
    }
};
