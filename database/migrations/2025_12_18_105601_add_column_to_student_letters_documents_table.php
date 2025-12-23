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
        Schema::table('student_letters_documents', function (Blueprint $table) {
            
            $table->smallInteger('mail_sent_status')->default(0)->after('updated_by');
            $table->timestamp('email_sent_at')->nullable()->after('mail_sent_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_letters_documents', function (Blueprint $table) {
            $table->dropColumn('email_sent_at');
            $table->dropColumn('mail_sent_status');
        });
    }
};
