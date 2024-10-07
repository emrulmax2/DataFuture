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
        Schema::table('student_notes', function (Blueprint $table) {
            $table->enum('is_flaged', ['Yes', 'No'])->default('No')->nullable()->after('followed_up_status');
            $table->enum('flaged_status', ['Active', 'Cleared'])->default(null)->nullable()->after('student_flag_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_notes', function (Blueprint $table) {
            //
        });
    }
};
