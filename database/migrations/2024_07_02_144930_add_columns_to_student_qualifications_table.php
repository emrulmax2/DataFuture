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
        Schema::table('student_qualifications', function (Blueprint $table) {

            $table->unsignedBigInteger('highest_qualification_on_entry_id')->nullable()->after('degree_award_date');
            $table->unsignedBigInteger('hesa_qualification_subject_id')->nullable()->after('highest_qualification_on_entry_id');
            $table->unsignedBigInteger('qualification_type_identifier_id')->nullable()->after('hesa_qualification_subject_id');
            $table->unsignedBigInteger('previous_provider_id')->nullable()->after('qualification_type_identifier_id');
            $table->unsignedBigInteger('hesa_exam_sitting_venue_id')->nullable()->after('previous_provider_id');
            $table->foreign('hesa_qualification_subject_id', 'hesa_qual_sub_id')->references('id')->on('hesa_qualification_subjects')->onDelete('set null')->onUpdate('set null');
            $table->foreign('highest_qualification_on_entry_id', 'stqual_high_qual_id')->references('id')->on('highest_qualification_on_entries')->onDelete('set null')->onUpdate('set null');
            $table->foreign('qualification_type_identifier_id', 'stqual_qual_type_id')->references('id')->on('qualification_type_identifiers')->onDelete('set null')->onUpdate('set null');
            $table->foreign('previous_provider_id', 'stqual_prev_prov_id')->references('id')->on('previous_providers')->onDelete('set null')->onUpdate('set null');

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_qualifications', function (Blueprint $table) {
            $table->dropForeign(['hesa_qual_sub_id']);
            $table->dropColumn('hesa_qualification_subject_id');
            $table->dropForeign(['stqual_high_qual_id']);
            $table->dropColumn('highest_qualification_on_entry_id');
            $table->dropForeign(['stqual_qual_type_id']);
            $table->dropColumn('qualification_type_identifier_id');
            $table->dropForeign(['stqual_prev_prov_id']);
            $table->dropColumn('previous_provider_id');
            $table->dropColumn('hesa_exam_sitting_venue_id');
        });

    }
};
