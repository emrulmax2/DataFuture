<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * The Operations ticket a course change request was raised as.
 *
 * The request is stored here first and the ticket raised second, so these
 * columns are how a request that never reached Operations is found again:
 * `ticket_status` says where it got to and `ticket_failed_reason` says why it
 * stopped. Nothing is ever lost silently.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_course_change_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('ticket_id')->nullable()->after('status');
            $table->string('ticket_ref', 32)->nullable()->after('ticket_id');
            /* queued - not sent yet; raised - Operations has it; failed - it did not. */
            $table->string('ticket_status', 16)->default('queued')->after('ticket_ref');
            $table->text('ticket_failed_reason')->nullable()->after('ticket_status');
            $table->timestamp('ticket_raised_at')->nullable()->after('ticket_failed_reason');
            $table->timestamp('student_notified_at')->nullable()->after('ticket_raised_at');

            $table->index('ticket_ref');
            $table->index('ticket_status');
        });
    }

    public function down(): void
    {
        Schema::table('student_course_change_requests', function (Blueprint $table) {
            $table->dropIndex(['ticket_ref']);
            $table->dropIndex(['ticket_status']);
            $table->dropColumn([
                'ticket_id', 'ticket_ref', 'ticket_status',
                'ticket_failed_reason', 'ticket_raised_at', 'student_notified_at',
            ]);
        });
    }
};
