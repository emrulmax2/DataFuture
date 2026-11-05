<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * /slc-registration/multiple-agreements › "Reviewed & Hide" (user, 5 Nov 2026): a group of duplicate
 * agreements (same student, course relation and year) someone has looked at, with the agreements it
 * had then. The group stays hidden from the list and the Dashboard count while it has exactly those
 * agreements; one added or removed brings it back.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slc_agreement_duplicate_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('student_course_relation_id')->nullable();
            $table->integer('year')->nullable();
            $table->json('agreement_ids');
            $table->boolean('hidden')->default(true);
            $table->unsignedBigInteger('reviewed_by');
            $table->timestamp('reviewed_at');
            $table->timestamps();
            $table->index(['student_id', 'student_course_relation_id', 'year'], 'slc_agr_dup_review_group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slc_agreement_duplicate_reviews');
    }
};
