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
        Schema::table('university_payment_claims', function (Blueprint $table) {
            $table->foreignId('semester_id')->nullable()->after('id')->constrained('semesters')->nullOnDelete();
            $table->foreignId('course_id')->nullable()->after('id')->constrained('courses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('university_payment_claims', function (Blueprint $table) {
            //
        });
    }
};
