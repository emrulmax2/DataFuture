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
        Schema::table('pay_slip_upload_syncs', function (Blueprint $table) {
            $table->string('type')->nullable()->after('employee_id');   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pay_slip_upload_syncs', function (Blueprint $table) {
            //
        });
    }
};
