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
        Schema::create('employee_apprisal_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_appraisal_id');
            $table->unsignedBigInteger('employee_document_id');

            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('employee_appraisal_id', 'emp_aprs_doc_fk')->references('id')->on('employee_appraisals')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('employee_document_id', 'emp_aprs_doc2_fk')->references('id')->on('employee_documents')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_apprisal_documents');
    }
};
