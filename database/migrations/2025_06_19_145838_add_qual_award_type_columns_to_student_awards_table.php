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
        Schema::table('student_awards', function (Blueprint $table) {
            $table->string('qual_award_type', 20)->nullable()->after('date_of_award');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_awards', function (Blueprint $table) {
            //
        });
    }
};
