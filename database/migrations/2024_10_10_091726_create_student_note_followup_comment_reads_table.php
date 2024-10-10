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
        Schema::create('student_note_followup_comment_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_note_id');
            $table->unsignedBigInteger('student_note_followup_comment_id');
            $table->unsignedBigInteger('user_id');
            $table->smallInteger('read')->nullable()->default(0)->comment('0=Unread,1=Read');
            $table->dateTime('readed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_note_followup_comment_reads');
    }
};
