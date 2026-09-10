<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets `library_deposits` hold fine payments as well as the refundable bond.
 *
 * The table was one row per deposit attempt. The desk also takes money for
 * overdue charges, and that had nowhere to live: `library_book_issues` records
 * what a loan was *charged* (`fine_amount`, `fine_paid_at`) but not the payment
 * itself — no amount tendered, no method, no reference, and nothing at all for
 * a part payment or a fine settled across two visits.
 *
 * So this becomes the money ledger for the library: `type` says which kind of
 * row it is, and a fine row points back at the loan it settles.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('library_deposits', function (Blueprint $table) {
            /* Defaulted to `deposit`, which backfills every existing row
               correctly — until now the table could hold nothing else. */
            $table->string('type', 20)->default('deposit')->after('student_id');

            /* Which loan a fine settles. Null on a deposit, and null is also
               allowed on a fine: a desk can take money against a charge whose
               loan row has since been tidied away, and losing the payment
               would be worse than losing the link. */
            $table->unsignedBigInteger('library_book_issue_id')->nullable()->after('type');

            /* What the money was for, in the words the desk used — "Replacement
               copy", "Overdue, waived £2". Free text on purpose: the reason is
               for a human reading the ledger later. */
            $table->string('description', 191)->nullable()->after('status');

            /* Who took it, when it is not a PayPal capture. */
            $table->unsignedBigInteger('collected_by')->nullable()->after('payer_email');

            $table->index(['student_id', 'type', 'status'], 'library_deposits_student_type_status_idx');
            $table->index('library_book_issue_id', 'library_deposits_issue_idx');
        });
    }

    public function down(): void
    {
        Schema::table('library_deposits', function (Blueprint $table) {
            $table->dropIndex('library_deposits_student_type_status_idx');
            $table->dropIndex('library_deposits_issue_idx');
            $table->dropColumn(['type', 'library_book_issue_id', 'description', 'collected_by']);
        });
    }
};
