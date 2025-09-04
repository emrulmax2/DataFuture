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
        Schema::create('student_address_update_request_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_address_update_request_id')->nullable();
            $table->foreign('student_address_update_request_id', 'fk_saurn_id')->references('id')->on('student_address_update_requests')->onDelete('set null');
            $table->text('note')->nullable();
            

            $table->enum('status', ['Pending', 'Approved', 'Cancelled'])->default('Pending');
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
        Schema::dropIfExists('student_address_update_request_notes');
    }
};
