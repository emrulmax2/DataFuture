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
        Schema::table('report_it_alls', function (Blueprint $table) {
            $table->unsignedBigInteger('task_list_id')->nullable()->after('description');
            $table->foreign('task_list_id')->references('id')->on('task_lists')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_it_alls', function (Blueprint $table) {
            
            $table->dropForeign(['task_list_id']);
            $table->dropColumn('task_list_id');
        });
    }
};
