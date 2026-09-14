<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One table for the whole borrow lifecycle, under the `library_` prefix.
 *
 * The student raises the row when they book; library staff act on the same row
 * when they hand the book over and when it comes back. Splitting "request" and
 * "issue" into two tables would mean reconciling them forever — the desk and
 * the student are looking at one event.
 *
 * Both tables are empty, so the rename costs nothing now and never again.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('lib_deposits', 'library_deposits');
        Schema::rename('lib_loans', 'library_book_issues');

        Schema::table('library_book_issues', function (Blueprint $table) {
            /* Quoted at the desk and printed on the hold slip — an id is not
               something staff can read back to a student over a counter. */
            $table->string('reference', 32)->nullable()->unique()->after('id');

            $table->unsignedBigInteger('issued_by')->nullable()->after('renewals');
            $table->unsignedBigInteger('returned_by')->nullable()->after('issued_by');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('returned_by');
            $table->string('cancel_reason', 191)->nullable()->after('cancelled_by');
            $table->text('staff_note')->nullable()->after('cancel_reason');

            /* A booked copy sits off the shelf until collected. Without a
               deadline an uncollected request strands that copy for good. */
            $table->timestamp('expires_at')->nullable()->index()->after('requested_at');
        });

        /* `on_loan` was doing two jobs — waiting on the hold shelf, and out in
           a student's bag. The desk cannot run without telling those apart. */
        \Illuminate\Support\Facades\DB::table('library_book_issues')
            ->where('status', 'on_loan')
            ->update(['status' => 'issued']);
    }

    public function down(): void
    {
        Schema::table('library_book_issues', function (Blueprint $table) {
            $table->dropUnique(['reference']);
            $table->dropIndex(['expires_at']);
            $table->dropColumn([
                'reference', 'issued_by', 'returned_by', 'cancelled_by',
                'cancel_reason', 'staff_note', 'expires_at',
            ]);
        });

        Schema::rename('library_book_issues', 'lib_loans');
        Schema::rename('library_deposits', 'lib_deposits');
    }
};
