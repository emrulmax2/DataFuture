<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_orders', function (Blueprint $table) {
            $table->timestamp('transaction_date')
                ->after('total_amount') // Add after the 'created_at' column
                ->nullable()
                ->comment('Date of the transaction');
            $table->string('transaction_id')
                ->after('transaction_date') // Add after the 'transaction_date' column
                ->nullable()
                ->comment('Unique identifier for the transaction');
            $table->enum('payment_status',['Pending','Completed','Failed'])
                ->after('transaction_id') // Add after the 'transaction_id' column
                ->nullable()
                ->comment('Status of the payment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_orders', function (Blueprint $table) {
            //
        });
    }
};
