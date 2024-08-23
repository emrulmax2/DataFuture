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
        Schema::create('student_sms_contents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sms_template_id')->nullable();
            $table->text('subject')->nullable();
            $table->text('sms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_sms_contents');
    }
};
