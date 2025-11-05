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
        Schema::table('budget_requisitions', function (Blueprint $table) {
            $table->smallInteger('is_force_complete')->default(0)->comment('0 = No, 1 = Yes')->after('note');
            $table->unsignedBigInteger('force_completed_by')->nullable()->after('is_force_complete');
            $table->dateTime('force_completed_at')->nullable()->after('force_completed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_requisitions', function (Blueprint $table) {
            //
        });
    }
};
