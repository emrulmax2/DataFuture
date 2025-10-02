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
        Schema::table('report_it_all_uploads', function (Blueprint $table) {
            $table->dropForeign(['report_it_all_id']);
            $table->unsignedBigInteger('report_it_all_id')->nullable()->change();
            $table->foreign('report_it_all_id')->references('id')->on('report_it_alls')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_it_all_uploads', function (Blueprint $table) {
            $table->dropForeign(['report_it_all_id']);
            $table->unsignedBigInteger('report_it_all_id')->nullable(false)->change();
            $table->foreign('report_it_all_id')->references('id')->on('report_it_alls')->onDelete('cascade');
        });
    }
};
