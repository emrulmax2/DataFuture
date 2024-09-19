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
        Schema::table('attendance_excuses', function (Blueprint $table) {
            $table->unsignedBigInteger('student_task_id')->nullable()->after('student_id');
            
            $table->foreign('student_task_id')->references('id')->on('student_tasks')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_excuses', function (Blueprint $table) {
            //
        });
    }
};
