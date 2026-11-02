<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bulk payment upload › "Bring into this tab" (user, 2 Nov 2026): a payment still waiting in
 * Reports › Accounts › Payments Upload (slc_payment_histories) brought in as an upload line keeps
 * the waiting entry it came from, so recording it here clears it there.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slc_payment_upload_rows', function (Blueprint $table) {
            $table->unsignedBigInteger('slc_payment_history_id')->nullable()->after('slc_payment_upload_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('slc_payment_upload_rows', function (Blueprint $table) {
            $table->dropIndex(['slc_payment_history_id']);
            $table->dropColumn('slc_payment_history_id');
        });
    }
};
