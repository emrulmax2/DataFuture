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
        Schema::create('budget_requisitions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_year_id');
            $table->unsignedBigInteger('budget_set_id');
            $table->unsignedBigInteger('vendor_id');
            $table->date('date');
            $table->unsignedBigInteger('requisitioner')->nullable();
            $table->unsignedBigInteger('budget_set_detail_id')->nullable();
            $table->date('required_by')->nullable();
            $table->unsignedBigInteger('venue_id')->nullable();
            $table->unsignedBigInteger('first_approver')->nullable();
            $table->unsignedBigInteger('final_approver')->nullable();
            $table->text('note')->nullable();
            $table->smallInteger('active')->default(1)->comment('0=Canceled, 1=Just In, 2=First Approval, 3=Approved, 4=Completed');

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_requisitions');
    }
};
