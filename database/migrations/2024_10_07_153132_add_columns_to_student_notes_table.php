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
        Schema::table('student_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('followup_completed_by')->nullable()->after('followed_up_status');
            $table->dateTime('followup_completed_at')->nullable()->after('followup_completed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_notes', function (Blueprint $table) {
            //
        });
    }
};
