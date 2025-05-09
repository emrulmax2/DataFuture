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
            
            $table->foreignId('student_id')
                ->after('id') // Add after the 'id' column
                ->constrained('students')
                ->cascadeOnDelete()
                ->comment('Foreign key to the students table');

            $table->double('sub_amount', 8, 2)->default(0)->nullable()
                ->after('student_id') // Add after 'student_id'
                ->comment('Subtotal amount of the order');

            $table->double('tax_amount', 8, 2)->default(0)->nullable()
                ->after('sub_amount') // Add after 'sub_amount'
                ->comment('Tax amount of the order');

            $table->double('total_amount', 8, 2)->default(0)->nullable()
                ->after('tax_amount') // Add after 'tax_amount'
                ->comment('Total amount of the order');

            $table->enum('payment_method',['Card','PayPal','N/A'])->default('N/A')
                ->after('total_amount') // Add after 'total_amount'
                ->comment('Payment method used for the order');

            $table->enum('status', ['Pending', 'In Progress', 'Approved', 'Rejected', 'Completed'])
                ->after('payment_method') // Add after 'payment_method'
                ->comment('Status of the order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_orders', function (Blueprint $table) {
            
            // Drop the 'student_id' column
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');

            // Drop the 'sub_amount' column
            $table->dropColumn('sub_amount');

            // Drop the 'tax_amount' column
            $table->dropColumn('tax_amount');

            // Drop the 'total_amount' column
            $table->dropColumn('total_amount');

            // Drop the 'payment_method' column
            $table->dropColumn('payment_method');

            // Drop the 'status' column
            $table->dropColumn('status');
        });
    }
};
