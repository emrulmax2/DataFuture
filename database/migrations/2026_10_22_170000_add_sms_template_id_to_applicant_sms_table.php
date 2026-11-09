<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ApplicantSms::create() and its template() relation already use
 * sms_template_id, but the column was never added, so sending an SMS from
 * the admission communication page failed with "Unknown column".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('applicant_sms', 'sms_template_id')) {
            Schema::table('applicant_sms', function (Blueprint $table) {
                $table->unsignedBigInteger('sms_template_id')->nullable()->after('applicant_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('applicant_sms', 'sms_template_id')) {
            Schema::table('applicant_sms', function (Blueprint $table) {
                $table->dropColumn('sms_template_id');
            });
        }
    }
};
