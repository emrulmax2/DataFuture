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
        Schema::create('document_info_reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_info_id');
            $table->string('subject', 191);
            $table->text('message');
            $table->smallInteger('is_repeat_reminder')->default(0)->comment('1=Yes,0=No');
            $table->smallInteger('is_send_email')->default(0)->comment('1=Yes,0=No');
            $table->date('single_reminder_date')->nullable();
            $table->enum('frequency', ['Daily', 'Weekly', 'Monthly', 'Quarterly', 'Half Yearly', 'Yearly'])->nullable();
            $table->date('repeat_reminder_start')->nullable();
            $table->date('repeat_reminder_end')->nullable();


            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('document_info_id')->references('id')->on('document_infos')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_info_reminders');
    }
};
