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
        Schema::create('student_notes', function (Blueprint $table) {
            $table->id();
            $table->index('student_id');
            $table->bigInteger('student_id')->unsigned();
            $table->index('student_document_id');
            $table->bigInteger('student_document_id')->unsigned();
            $table->longText('note');
            $table->enum('phase',['Applicant', 'Admission', 'Register', 'Live'])->default('Admission');
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('student_document_id')->references('id')->on('student_documents')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::dropIfExists('student_notes');
    }
};
