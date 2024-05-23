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
        Schema::table('term_declarations', function (Blueprint $table) {

            $table->date('start_date')->nullable()->after('term_type_id');
            $table->date('end_date')->nullable()->after('start_date');
            $table->integer('total_teaching_weeks')->nullable()->after('end_date');
            $table->date('teaching_start_date')->nullable()->after('total_teaching_weeks');
            $table->date('teaching_end_date')->nullable()->after('teaching_start_date');
            $table->date('revision_start_date')->nullable()->after('teaching_end_date');
            $table->date('revision_end_date')->nullable()->after('revision_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('term_declarations', function (Blueprint $table) {
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
            $table->dropColumn('total_teaching_weeks');
            $table->dropColumn('teaching_start_date');
            $table->dropColumn('teaching_end_date');
            $table->dropColumn('revision_start_date');
            $table->dropColumn('revision_end_date');
        });
    }
};
