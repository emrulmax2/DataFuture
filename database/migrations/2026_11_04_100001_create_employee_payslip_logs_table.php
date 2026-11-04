<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail for payslip, P45 and P60 files: who uploaded, replaced or
 * deleted one, for whom, and who was really behind an impersonated session.
 *
 * Guarded and add-only, so it is safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        if(!Schema::hasTable('employee_payslip_logs')):
            Schema::create('employee_payslip_logs', function (Blueprint $table) {
                $table->id();
                /* No foreign key on purpose: the line must outlive a deleted payslip. */
                $table->bigInteger('pay_slip_upload_sync_id')->unsigned()->nullable();
                /* Whose payslip it is. */
                $table->bigInteger('employee_id')->unsigned()->nullable();
                /* The account that was signed in; NULL only if a queued upload had no owner. */
                $table->bigInteger('user_id')->unsigned()->nullable();
                /* The person really at the keyboard when user_id was being impersonated. */
                $table->bigInteger('impersonator_id')->unsigned()->nullable();
                $table->string('event', 20);
                /* Kept on the line so the trail still reads after the payslip is gone. */
                $table->string('type', 20)->nullable();
                $table->string('month_year', 10)->nullable();
                $table->bigInteger('holiday_year_id')->unsigned()->nullable();
                $table->string('file_name', 191)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->timestamps();

                $table->index(['employee_id', 'created_at'], 'epl_employee_idx');
                $table->index(['user_id', 'created_at'], 'epl_user_idx');
                $table->index('pay_slip_upload_sync_id', 'epl_payslip_idx');
            });
        endif;
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_payslip_logs');
    }
};
