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
        Schema::create('budget_requisition_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_requisition_id');
            $table->text('description')->nullable();
            $table->integer('quantity')->nullable();
            $table->double('price', 10, 2);
            $table->double('total', 10, 2);
            $table->smallInteger('active')->default(1)->comment('0=Inactive, 1=Active');

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('budget_requisition_id')->references('id')->on('budget_requisitions')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_requisition_items');
    }
};
