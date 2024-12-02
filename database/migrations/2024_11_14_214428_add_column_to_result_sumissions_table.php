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
        Schema::table('result_submissions', function (Blueprint $table) {
            $table->smallInteger('is_student_matched')->nullable()->after('grade_id')->default(1);
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('result_sumissions', function (Blueprint $table) {
            //
        });
    }
};
