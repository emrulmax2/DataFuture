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
            $table->smallInteger('active')->default(1)->comment('0=Cancelled, 1=New, 2=First Approval Done, 3=Second Approval Done, 4=Awaiting Payment, 5=Paid')->change();
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
