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
        Schema::create('employee_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->enum('pay_frequency', ['Monthly', 'Weekly'])->nullable();
            $table->string('tax_code', 191)->nullable();
            $table->enum('payment_method', ['Bank Transfer', 'Cash', 'Cheque'])->nullable();
            $table->enum('subject_to_clockin', ['Yes', 'No'])->nullable();
            $table->decimal('holiday_base', 10, 2)->nullable();
            $table->enum('bank_holiday_auto_book', ['Yes', 'No'])->nullable();
            $table->enum('pension_enrolled', ['Yes', 'No'])->nullable();
            
            $table->bigInteger('created_by')->unsigned();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_payment_settings');
    }
};
