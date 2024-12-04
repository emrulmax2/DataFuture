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
        Schema::table('result_submission_by_staff', function (Blueprint $table) {
            $table->unsignedBigInteger('result_id')->nullable()->after('id');
            $table->string('paper_id',191)->nullable()->after('grade_id');
            //$table->foreign('result_id','result_submissionstaff_result_id')->references('id')->on('results')->onUpdate('set null')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('result_submission_by_staff', function (Blueprint $table) {
            
            $table->dropColumn('result_id');
        });
    }
};
