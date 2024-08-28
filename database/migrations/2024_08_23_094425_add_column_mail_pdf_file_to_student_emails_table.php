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
        Schema::table('student_emails', function (Blueprint $table) {
            $table->dropColumn('body');
            $table->string('mail_pdf_file', 191)->nullable()->after('subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_emails', function (Blueprint $table) {
            $table->dropColumn('mail_pdf_file');
            $table->longText('body')->after('subject')->nullable();
        });
    }
};
