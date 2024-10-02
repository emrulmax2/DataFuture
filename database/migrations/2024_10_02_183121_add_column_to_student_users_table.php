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
        Schema::table('student_users', function (Blueprint $table) {
            $table->string('temp_email')->nullable()->after('active');
            $table->string('temp_email_verify_code')->nullable()->after('temp_email');
            $table->string('temp_mobile')->nullable()->after('temp_email');
            $table->string('temp_mobile_verify_code')->nullable()->after('temp_mobile');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_users', function (Blueprint $table) {
            $table->dropColumn('temp_email');
            $table->string('temp_email_verify_code');
            $table->dropColumn('temp_mobile');
            $table->dropColumn('temp_mobile_verify_code');
        });
    }
};
