<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Day reading, and a trail of everything that happens to an issue.
 *
 * Day reading is a book read in the library and handed back the same day. It is
 * the same event as a take-home loan — a copy leaves the shelf and comes back —
 * so it lives in the same table under a type, rather than in a parallel one
 * that would need reconciling.
 *
 * The issue row carries the current state. The log carries how it got there:
 * who reserved it, who handed it over, who took it back, and when. Timestamps
 * on the row alone cannot answer "who cancelled this and why" after the fact.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('library_book_issues', function (Blueprint $table) {
            // take_home | day_reading
            $table->string('loan_type', 20)->default('take_home')->after('student_id')->index();
        });

        Schema::create('library_book_issue_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_book_issue_id')
                ->constrained('library_book_issues')
                ->cascadeOnDelete();

            // requested | issued | returned | cancelled | not_collected | renewed | note
            $table->string('action', 30)->index();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20)->nullable();

            /* Who did it. The id alone is ambiguous — students and staff are
               different tables — so the actor type is stored with it. */
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->string('performed_by_type', 20)->default('staff'); // staff | student | system
            $table->string('performed_by_name', 191)->nullable();

            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->index(['library_book_issue_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_book_issue_logs');

        Schema::table('library_book_issues', function (Blueprint $table) {
            $table->dropIndex(['loan_type']);
            $table->dropColumn('loan_type');
        });
    }
};
