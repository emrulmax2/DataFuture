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
        Schema::create('employee_notes', function (Blueprint $table) {
            $table->id();
            $table->index('employee_id');
            $table->bigInteger('employee_id')->unsigned();
            $table->index('employee_document_id');
            $table->bigInteger('employee_document_id')->unsigned()->nullable();
            $table->date('opening_date')->nullable();
            $table->longText('note');
            $table->enum('phase',['Applicant', 'Admission', 'Register', 'Live', 'Staff', 'Student Profile'])->default('Admission');
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('employee_document_id')->references('id')->on('employee_documents')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_notes');
    }
};
