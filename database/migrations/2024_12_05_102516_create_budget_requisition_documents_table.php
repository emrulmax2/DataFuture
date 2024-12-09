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
        Schema::create('budget_requisition_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_requisition_id')->unsigned();
            $table->string('display_file_name', 191);
            $table->smallInteger('hard_copy_check')->default(1)->nullable();
            $table->string('doc_type', 145)->nullable();
            $table->string('disk_type', 145)->nullable();
            $table->string('current_file_name', 191);

            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('budget_requisition_id')->references('id')->on('budget_requisitions')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_requisition_documents');
    }
};
