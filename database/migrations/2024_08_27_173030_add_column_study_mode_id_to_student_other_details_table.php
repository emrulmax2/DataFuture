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
        Schema::table('student_other_details', function (Blueprint $table) {
            $table->unsignedBigInteger('study_mode_id')->default(1)->nullable()->after('hesa_gender_id');
            $table->foreign('study_mode_id')->references('id')->on('study_modes')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_other_details', function (Blueprint $table) {
            $table->dropColumn('study_mode_id');
        });
    }
};
