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
        Schema::create('agent_comissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('agent_comission_rule_id');
            $table->unsignedBigInteger('slc_money_receipt_id');

            $table->double('receipt_amount', 10, 2);
            $table->double('comission', 10, 2);
            $table->date('paid_date')->nullable();
            $table->double('paid_amount', 10, 2)->nullable();
            $table->string('remittance_ref', 99)->nullable();
            $table->smallInteger('status')->nullable()->default(1)->comment('1=Unpaid, 2=Paid');

            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('agent_comission_rule_id')->references('id')->on('agent_comission_rules')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('slc_money_receipt_id')->references('id')->on('slc_money_receipts')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_comissions');
    }
};
