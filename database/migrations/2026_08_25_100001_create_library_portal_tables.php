<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Student-facing library borrowing.
 *
 * The catalogue itself lives in Operations (cam_library_titles / _copies) and
 * is read over the API — nothing here duplicates it. These two tables hold only
 * what belongs to this side: the student's refundable deposit, and the loans
 * they have taken out.
 *
 * Title and copy details are snapshotted onto the loan on purpose. A loan has
 * to render its book on "My books" years later even if Operations is down, the
 * title was re-catalogued, or the copy was withdrawn.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lib_deposits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->index();

            $table->decimal('amount', 8, 2);
            $table->string('currency', 3)->default('GBP');

            // pending  - checkout opened, not yet confirmed by Stripe
            // paid     - held; the student may borrow
            // refunded - returned to the student, borrowing locked again
            // failed   - checkout abandoned or declined
            $table->string('status', 20)->default('pending')->index();

            $table->string('stripe_session_id', 255)->nullable()->unique();
            $table->string('stripe_payment_intent_id', 255)->nullable()->index();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->text('failure_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lib_loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->index();

            /* Operations identifiers. Not foreign keys — they point at another
               database, so they are stored as plain references. */
            $table->unsignedBigInteger('ops_title_id')->index();
            $table->unsignedBigInteger('ops_copy_id')->nullable()->index();
            $table->string('barcode', 120)->nullable()->index();

            /* Snapshot of the book as it was when borrowed. */
            $table->string('title', 255);
            $table->string('author', 255)->nullable();
            $table->string('isbn13', 32)->nullable();
            $table->string('campus', 191)->nullable();
            $table->string('location', 191)->nullable();
            $table->decimal('book_price', 10, 2)->nullable();

            // requested - student booked it, waiting for desk collection
            // on_loan   - handed over
            // returned  - back on the shelf
            // cancelled - not collected / withdrawn
            $table->string('status', 20)->default('requested')->index();

            $table->timestamp('requested_at')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->date('due_at')->nullable()->index();
            $table->timestamp('returned_at')->nullable();

            $table->unsignedTinyInteger('renewals')->default(0);

            /* Settled fine. The live figure for an open loan is calculated from
               due_at and the rules in options, never stored, so changing the
               penalty does not silently rewrite what someone already owes. */
            $table->decimal('fine_amount', 8, 2)->default(0);
            $table->timestamp('fine_paid_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lib_loans');
        Schema::dropIfExists('lib_deposits');
    }
};
