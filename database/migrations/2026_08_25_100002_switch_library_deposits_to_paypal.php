<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Deposits are collected through PayPal, not Stripe.
 *
 * The columns are named for the role rather than the provider — order id,
 * capture id, refund id — with `provider` recording who took the money. Adding
 * a second provider later is then a value, not another migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lib_deposits', function (Blueprint $table) {
            $table->dropUnique(['stripe_session_id']);
            $table->dropIndex(['stripe_payment_intent_id']);
            $table->dropColumn(['stripe_session_id', 'stripe_payment_intent_id']);
        });

        Schema::table('lib_deposits', function (Blueprint $table) {
            $table->string('provider', 20)->default('paypal')->after('currency');

            /* The order the student was sent to approve. Unique so a replayed
               return URL cannot settle the same order onto two rows. */
            $table->string('provider_order_id', 255)->nullable()->unique()->after('provider');

            // Set once the money is actually captured — this is the receipt.
            $table->string('provider_capture_id', 255)->nullable()->index()->after('provider_order_id');
            $table->string('provider_refund_id', 255)->nullable()->index()->after('provider_capture_id');

            $table->string('payer_email', 191)->nullable()->after('provider_refund_id');
        });
    }

    public function down(): void
    {
        Schema::table('lib_deposits', function (Blueprint $table) {
            $table->dropColumn([
                'provider', 'provider_order_id', 'provider_capture_id',
                'provider_refund_id', 'payer_email',
            ]);
        });

        Schema::table('lib_deposits', function (Blueprint $table) {
            $table->string('stripe_session_id', 255)->nullable()->unique();
            $table->string('stripe_payment_intent_id', 255)->nullable()->index();
        });
    }
};
