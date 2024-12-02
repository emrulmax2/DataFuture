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
            $table->smallInteger('is_it_final')->default(0)->after('is_student_matched');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('result_submissions', function (Blueprint $table) {
            $table->dropColumn('is_it_final');
        });
    }
};
