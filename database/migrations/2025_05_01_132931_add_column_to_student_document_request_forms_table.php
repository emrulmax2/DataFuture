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
        Schema::table('student_document_request_forms', function (Blueprint $table) {
            
            $table->double('total_amount', 8, 2)->default(0)->after('student_consent')->nullable();
            $table->double('paid_amount', 8, 2)->default(0)->after('total_amount')->nullable();
            $table->double('remaining_amount', 8, 2)->default(0)->after('paid_amount')->nullable();
            $table->enum('payment_status', ['paid', 'unpaid'])->default('unpaid')->after('remaining_amount')->nullable();
            $table->enum('payment_method',['paypal','card','cash'])->default('cash')->after('payment_status')->nullable();
            $table->string('payment_reference')->after('payment_method')->nullable();
            $table->date('payment_date')->after('payment_reference')->nullable();
            $table->string('payment_receipt')->after('payment_date')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_document_request_forms', function (Blueprint $table) {
            $table->dropColumn('total_amount');
            $table->dropColumn('paid_amount');
            $table->dropColumn('remaining_amount');
            $table->dropColumn('payment_status');
            $table->dropColumn('payment_method');
            $table->dropColumn('payment_reference');
            $table->dropColumn('payment_date');
            $table->dropColumn('payment_receipt');
        });
    }
};
