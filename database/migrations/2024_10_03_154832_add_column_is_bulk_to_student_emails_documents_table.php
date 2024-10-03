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
        Schema::table('student_emails_documents', function (Blueprint $table) {
            $table->smallInteger('is_bulk')->default(0)->after('student_email_id')->comment('0 = No, 1 = Yes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_emails_documents', function (Blueprint $table) {
            $table->dropColumn('is_bulk');
        });
    }
};
