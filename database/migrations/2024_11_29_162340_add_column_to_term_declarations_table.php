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
            $table->date('exam_publish_date')->nullable()->after('revision_end_date');
            $table->time('exam_publish_time')->nullable()->after('exam_publish_date');
            $table->date('exam_resubmission_publish_date')->nullable()->after('exam_publish_time');
            $table->time('exam_resubmission_publish_time')->nullable()->after('exam_resubmission_publish_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('term_declarations', function (Blueprint $table) {
            $table->dropColumn('exam_publish_date');
            $table->dropColumn('exam_publish_time');
            $table->dropColumn('exam_resubmission_publish_date');
            $table->dropColumn('exam_resubmission_publish_time');
        });
    }
};
