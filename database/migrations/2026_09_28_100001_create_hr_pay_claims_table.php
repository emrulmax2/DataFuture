<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payment claims pushed here from the Operations HR pay portal.
 *
 * Staff raise and approve claims over there; this is the copy DataFuture keeps
 * so the attendance report can say what a person claimed in a given month
 * alongside what they worked. The push happens when Accounts settles a batch —
 * so every row here is money already marked paid, not a claim in flight.
 *
 * The month is the *pay cycle*, not the period the claimant typed. `period` is
 * free text over there ("August & September 2026", "August and September
 * 2026") and cannot be relied on to name a month, whereas the pay cycle is
 * what payroll actually ran the money in.
 *
 * `reference` is the natural key. It is unique over there and unique here, so
 * a batch pushed twice — a retried request, a double click — updates the same
 * row instead of doubling the total on a report.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_pay_claims', function (Blueprint $table) {
            $table->id();

            /* Operations' own identifiers, kept so a figure queried here can be
               traced back to the submission it came from. */
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('reference', 64)->unique();
            $table->string('doc_type', 16)->default('claim');

            /* Resolved on arrival by matching the claimant's email to a user,
               and that user to an employee. Nullable because a claim whose
               person cannot be matched is still worth storing — losing the
               money would be worse than losing the link, and it can be
               reconciled later. */
            $table->unsignedBigInteger('employee_id')->nullable();

            /* Operations' payroll number. DataFuture holds no payroll number of
               its own, so this is recorded as given rather than matched on. */
            $table->string('payroll_number', 32)->nullable();
            $table->string('staff_name', 191)->nullable();
            $table->string('staff_email', 191)->nullable();

            /* First of the pay-cycle month, so a month is a range query rather
               than a string comparison against a formatted label. The label is
               kept beside it for display, exactly as Operations worded it. */
            $table->date('pay_cycle');
            $table->string('pay_cycle_label', 32)->nullable();

            /* What the claimant typed, kept verbatim. Not used for totals — see
               the note above — but it is what they will quote when querying a
               figure, so the desk needs to be able to see it. */
            $table->string('period', 191)->nullable();
            $table->text('for_summary')->nullable();

            $table->decimal('subtotal', 12, 2)->default(0);

            /* The claim lines as submitted: date, description, qty, rate. Held
               so this row can answer "what was that £347 for" without a call to
               Operations. */
            $table->json('lines')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            /* Who pushed it and when, for the same reason every sync here keeps
               it: a figure that appears on a report needs an answer to "where
               did this come from". */
            $table->timestamp('pushed_at')->nullable();
            $table->string('pushed_by_email', 191)->nullable();

            $table->timestamps();

            /* The report's only query: one month, grouped by person. */
            $table->index(['pay_cycle', 'employee_id'], 'hr_pay_claims_cycle_employee_idx');
            $table->index('payroll_number', 'hr_pay_claims_payroll_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_pay_claims');
    }
};
