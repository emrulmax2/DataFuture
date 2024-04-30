<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('acc_transactions', function (Blueprint $table) {
            $table->smallInteger('flow')->nullable()->after('transaction_type')->comment('0=Inflow,1=Outflow');
            $table->dropColumn('transfer_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('acc_transactions', function (Blueprint $table) {
            //
        });
    }
};
