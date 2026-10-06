<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Student app: card payments for document request orders.
 *
 *   student_order_payments          one row per attempt to pay an order from
 *                                   the app, so the app can ask how the last
 *                                   attempt ended after the payment page closes
 *   student_orders.idempotency_key  the key the app sends when it places an
 *                                   order, so a retried request returns the
 *                                   same order instead of failing on an empty
 *                                   basket
 *
 * Every step is guarded and only ever adds, so it is safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        if(!Schema::hasTable('student_order_payments')):
            Schema::create('student_order_payments', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('student_order_id')->unsigned();
                $table->bigInteger('student_id')->unsigned();
                $table->string('provider', 20)->default('stripe');
                /* The provider's checkout session, and the payment it produced. */
                $table->string('provider_session_id', 191)->nullable();
                $table->string('provider_payment_id', 191)->nullable();
                /* Names this attempt in the return link the provider sends the student back to. */
                $table->string('token', 64);
                $table->string('status', 20)->default('pending')->comment('pending, succeeded, failed, cancelled');
                $table->unsignedInteger('amount')->comment('In pence');
                $table->string('currency', 3)->default('GBP');
                $table->text('checkout_url')->nullable();
                /* Where the app asked to be sent afterwards, usually a deep link. */
                $table->text('return_url')->nullable();
                $table->dateTime('expires_at')->nullable();
                $table->dateTime('paid_at')->nullable();
                $table->string('failure_message', 191)->nullable();
                /* The student user who started the attempt. */
                $table->bigInteger('created_by')->unsigned()->nullable();
                $table->timestamps();

                $table->unique('token', 'sop_token_unique');
                $table->index('student_order_id', 'sop_order_idx');
                $table->index('provider_session_id', 'sop_session_idx');
            });
        endif;

        if(Schema::hasTable('student_orders') && !Schema::hasColumn('student_orders', 'idempotency_key')):
            Schema::table('student_orders', function (Blueprint $table) {
                $table->string('idempotency_key', 64)->nullable()->after('invoice_number');
                $table->unique(['student_id', 'idempotency_key'], 'so_student_idempotency_unique');
            });
        endif;
    }

    public function down(): void
    {
        if(Schema::hasTable('student_orders') && Schema::hasColumn('student_orders', 'idempotency_key')):
            Schema::table('student_orders', function (Blueprint $table) {
                $table->dropUnique('so_student_idempotency_unique');
                $table->dropColumn('idempotency_key');
            });
        endif;

        Schema::dropIfExists('student_order_payments');
    }
};
