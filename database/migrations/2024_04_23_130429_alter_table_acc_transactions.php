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
            $table->unsignedBigInteger('acc_category_id')->nullable()->change();
            $table->unsignedBigInteger('acc_method_id')->nullable()->change();
            $table->smallInteger('transaction_type')->nullable()->comment('0=Income, 1=Expense, 2=Transfer')->change();

            $table->unsignedBigInteger('transfer_id')->nullable()->after('audit_status');
            $table->smallInteger('transfer_type')->nullable()->after('transfer_id')->comment('0=Deposit, 1=Withdrawl');
            $table->unsignedBigInteger('transfer_bank_id')->nullable()->after('transfer_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
