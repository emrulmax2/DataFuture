<?php

use Google\Service\AndroidPublisher\Timestamp;
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
        Schema::table('result_comparisons', function (Blueprint $table) {
            //$table->dropColumn('result_Ids');
            $table->unsignedBigInteger('result_id')->nullable()->after('assessment_plan_id');
            $table->foreign('result_id','fk_comparison_result')->references('id')->on('results')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('result_comparisons', function (Blueprint $table) {
            $table->json('result_Ids')->nullable()->after('assessment_plan_id');
            $table->dropForeign('fk_comparison_result');
            $table->dropColumn('result_id');
        });
    }
};
