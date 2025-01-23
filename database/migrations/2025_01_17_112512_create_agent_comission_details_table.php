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
        Schema::create('agent_comission_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('agent_comission_id');
            $table->unsignedBigInteger('slc_money_receipt_id')->nullable();
            $table->double('amount', 10, 2);
            $table->smallInteger('status')->nullable()->default(1)->comment('1=Unpaid, 2=Paid');

            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('agent_comission_id')->references('id')->on('agent_comissions')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('slc_money_receipt_id')->references('id')->on('slc_money_receipts')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_comission_details');
    }
};
