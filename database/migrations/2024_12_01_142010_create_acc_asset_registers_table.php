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
        Schema::create('acc_asset_registers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('acc_transaction_id');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('acc_asset_type_id')->nullable();
            $table->text('location')->nullable();
            $table->string('serial')->nullable();
            $table->string('barcode', 50)->nullable();
            $table->integer('life')->nullable()->default(0);
            $table->smallInteger('active')->default(1)->comment('0=InActive, 1=New, 2=Active');

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('acc_transaction_id')->references('id')->on('acc_transactions')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acc_asset_registers');
    }
};
