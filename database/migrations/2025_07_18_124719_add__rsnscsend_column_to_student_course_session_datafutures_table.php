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
        Schema::table('student_course_session_datafutures', function (Blueprint $table) {
            $table->string('RSNSCSEND', 191)->nullable()->after('INVOICEHESAID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_course_session_datafutures', function (Blueprint $table) {
            //
        });
    }
};
