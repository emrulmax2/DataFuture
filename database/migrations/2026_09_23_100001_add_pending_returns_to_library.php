<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A return that is waiting on money.
 *
 * A book handed back late used to close the loan there and then, leaving the
 * charge behind as a debt on a closed row — which is a debt nobody chases. Now
 * the desk's submission is held instead: everything they typed is kept, the
 * loan stays open, and the payment is what completes it.
 *
 * Held only for the rest of the day. A loan still out is still accruing, and a
 * submission carrying yesterday's figure would settle the wrong amount — so an
 * unpaid one is swept away overnight, another day is added to the charge, and
 * the desk submits again against today's figure.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('library_book_issues', function (Blueprint $table) {
            /* When the desk pressed Confirm return. The loan is untouched —
               status, due date and the fine clock all carry on — so this is
               the only thing saying a return is waiting to complete. */
            $table->timestamp('pending_return_at')->nullable()->after('returned_by');
            $table->unsignedBigInteger('pending_return_by')->nullable()->after('pending_return_at');
            $table->string('pending_return_note', 255)->nullable()->after('pending_return_by');

            /* The charge as it stood at submission, kept apart from
               `fine_amount` because nothing has been charged yet: this is what
               the link is for, and it is what gets frozen onto the loan if the
               payment lands. */
            $table->decimal('pending_return_fine', 10, 2)->nullable()->after('pending_return_note');

            /* End of the day it was submitted. Past this the submission is
               void and the desk starts again against a larger charge. */
            $table->timestamp('pending_return_expires_at')->nullable()->after('pending_return_fine');

            $table->index('pending_return_expires_at', 'library_issues_pending_return_idx');
        });

        Schema::table('library_deposits', function (Blueprint $table) {
            /* A fine link dies with the submission it belongs to. Stored so
               the page can say so plainly rather than showing a signature
               error, and so the sweep can find them. */
            $table->timestamp('expires_at')->nullable()->after('paid_at');

            /* How the desk chose to send it — `email`, `sms`, or both. Kept
               because "did they ever get the link" is the first question asked
               when a charge goes unpaid. */
            $table->string('sent_via', 20)->nullable()->after('expires_at');
            $table->timestamp('sent_at')->nullable()->after('sent_via');
        });
    }

    public function down(): void
    {
        Schema::table('library_book_issues', function (Blueprint $table) {
            $table->dropIndex('library_issues_pending_return_idx');
            $table->dropColumn([
                'pending_return_at', 'pending_return_by', 'pending_return_note',
                'pending_return_fine', 'pending_return_expires_at',
            ]);
        });

        Schema::table('library_deposits', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'sent_via', 'sent_at']);
        });
    }
};
