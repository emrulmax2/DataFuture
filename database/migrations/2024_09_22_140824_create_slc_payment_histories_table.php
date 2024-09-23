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
        Schema::create('slc_payment_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->date('transaction_date')->nullable();
            $table->string('term_name', 20)->nullable();
            $table->string('ssn', 91)->nullable();
            $table->string('first_name', 191)->nullable();
            $table->string('last_name', 191)->nullable();
            $table->date('dob')->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->string('course_code', 91)->nullable();
            $table->text('course_name')->nullable();
            $table->integer('year')->nullable();
            $table->decimal('amount', 10, 2)->nullable()->default(0);
            $table->smallInteger('status')->nullable();
            $table->unsignedBigInteger('slc_money_receipt_id')->nullable();
            $table->smallInteger('error_code')->nullable()->default(1);
            $table->text('errors')->nullable();

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slc_payment_histories');
    }
};
