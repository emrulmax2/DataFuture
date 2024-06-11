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
        Schema::table('plan_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('module_creation_id')->after('plan_id');
            $table->dropForeign('plan_tasks_plan_id_foreign');
            $table->unsignedBigInteger('plan_id')->nullable()->change();
            $table->foreign('module_creation_id')->references('id')->on('module_creations')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_tasks', function (Blueprint $table) {
            $table->dropForeign('plan_tasks_plan_id_foreign');
            $table->dropForeign('plan_tasks_module_creation_id_foreign');
            $table->dropColumn('module_creation_id');
            $table->unsignedBigInteger('plan_id')->change();
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade')->onUpdate('cascade');
        });
    }
};
