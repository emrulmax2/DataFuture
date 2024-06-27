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
        Schema::table('applicant_proposed_courses', function (Blueprint $table) {
            $table->unsignedBigInteger('venue_id')->nullable()->after('academic_year_id');
            $table->foreign('venue_id')->references('id')->on('venues')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicant_proposed_courses', function (Blueprint $table) {

            $table->dropForeign(['venue_id']);
            $table->dropColumn('venue_id');

        });
    }
};
