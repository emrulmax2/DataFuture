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
            $table->foreignId('care_leaver_id')->nullable()->after('study_mode_id')->constrained('care_leavers', 'id', 'sod_clid_fk')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_other_details', function (Blueprint $table) {
            //
        });
    }
};
