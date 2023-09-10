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
        Schema::create('student_letters', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('student_id')->unsigned();
            $table->bigInteger('letter_set_id')->unsigned();
            $table->bigInteger('signatory_id')->unsigned();
            $table->bigInteger('applicant_document_id')->unsigned();
            $table->string('pin',191)->nullable();
            $table->tinyInteger('is_email_or_attachment')->default('1');
            $table->bigInteger('issued_by');
            $table->date('issued_date');
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('letter_set_id')->references('id')->on('letter_sets')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('signatory_id')->references('id')->on('signatories')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('applicant_document_id')->references('id')->on('applicant_documents')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        
    
        Schema::dropIfExists('student_letters');
    }
};
