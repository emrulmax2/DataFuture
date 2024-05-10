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
        Schema::table('student_contacts', function (Blueprint $table) {
            $table->string('institutional_email', 191)->nullable()->after('personal_email');
            $table->smallInteger('institutional_email_verification')->nullable()->after('institutional_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_contacts', function (Blueprint $table) {
            $table->dropColumn(['institutional_email', 'institutional_email_verification']);
        });
    }
};
