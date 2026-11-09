<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Course Creation › Induction Booking Link (user, 30 Oct 2026): the link students of this course
 * creation use to book their induction. Moved here from term declarations the same day.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('course_creations', 'induction_booking_link')) {
            Schema::table('course_creations', function (Blueprint $table) {
                $table->string('induction_booking_link', 500)->nullable()->after('has_evening_and_weekend');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('course_creations', 'induction_booking_link')) {
            Schema::table('course_creations', function (Blueprint $table) {
                $table->dropColumn('induction_booking_link');
            });
        }
    }
};
