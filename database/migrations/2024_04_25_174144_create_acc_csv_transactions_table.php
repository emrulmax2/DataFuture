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
        Schema::create('acc_csv_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('acc_csv_file_id');
            $table->date('trans_date')->nullable();
            $table->text('description')->nullable();
            $table->double('amount', 10, 2)->default(0);
            $table->smallInteger('transaction_type')->nullable();

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
        Schema::dropIfExists('acc_csv_transactions');
    }
};
