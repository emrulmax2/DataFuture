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
        Schema::create('budget_requisition_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_requisition_id');
            $table->unsignedBigInteger('acc_transaction_id');

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('budget_requisition_id')->references('id')->on('budget_requisitions')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('acc_transaction_id')->references('id')->on('acc_transactions')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_requisition_transactions');
    }
};
