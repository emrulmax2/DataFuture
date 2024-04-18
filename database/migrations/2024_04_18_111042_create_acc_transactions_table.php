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
        Schema::create('acc_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code', 50)->nullable();
            $table->string('audiotr_ansaction_code', 50)->nullable();
            $table->integer('transaction_date')->nullable();
            $table->date('transaction_date_2')->nullable();
            $table->string('cheque_no', 191)->nullable();
            $table->integer('cheque_date')->nullable();
            $table->string('invoice_no', 191)->nullable();
            $table->date('invoice_date')->nullable();
            $table->unsignedBigInteger('acc_category_id');
            $table->unsignedBigInteger('acc_bank_id');
            $table->unsignedBigInteger('acc_method_id');
            $table->tinyInteger('transaction_type')->nullable()->comment('0=Inflow, 1=Outflow');
            $table->text('detail')->nullable();
            $table->text('description')->nullable();
            $table->text('new_description')->nullable();
            $table->decimal('transaction_amount', 10, 2);
            $table->string('transaction_doc_name', 191)->nullable();
            $table->unsignedBigInteger('parent')->nullable()->default(0);
            $table->tinyInteger('audit_status')->nullable()->default(0);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acc_transactions');
    }
};
