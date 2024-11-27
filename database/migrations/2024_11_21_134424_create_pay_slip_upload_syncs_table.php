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
        Schema::create('pay_slip_upload_syncs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null')->onUpdate('set null');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('month_year');
            $table->smallInteger('is_file_exist')->nullable();
            $table->smallInteger('file_transffered')->nullable();
            $table->timestamp('file_transffered_at')->nullable();
            $table->smallInteger('is_file_uploaded')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_slip_upload_syncs');
    }
};
