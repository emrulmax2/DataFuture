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
        Schema::table('attendance_excuses', function (Blueprint $table) {
            $table->unsignedBigInteger('actioned_by')->nullable()->after('attendance_types');
            $table->dateTime('actioned_at')->nullable()->after('actioned_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_excuses', function (Blueprint $table) {
            //
        });
    }
};
