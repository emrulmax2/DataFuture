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
        Schema::create('slc_money_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('student_course_relation_id')->nullable();
            $table->unsignedBigInteger('course_creation_instance_id')->nullable();
            $table->unsignedBigInteger('slc_agreement_id');
            $table->unsignedBigInteger('term_declaration_id')->nullable();
            $table->unsignedTinyInteger('session_term')->nullable();

            $table->string('invoice_no', 50);
            $table->string('slc_coursecode', 50)->nullable();
            $table->unsignedBigInteger('slc_payment_method_id')->nullable();
            $table->date('entry_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->double('amount', 10, 2);
            $table->double('discount', 10, 2)->default(0);
            $table->enum('payment_type', ['Course Fee', 'Exam Fee', 'ID Card Fee', 'Photocopy Card Fee', 'Late Fee', 'Refund', 'Letter Request'])->nullable();
            $table->text('remarks')->nullable();


            $table->bigInteger('received_by')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('student_course_relation_id', 'slc_inv_std_crel_id_fnk')->references('id')->on('student_course_relations')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('course_creation_instance_id', 'slc_inv_cci_id_fnk')->references('id')->on('course_creation_instances')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('slc_agreement_id')->references('id')->on('slc_agreements')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('term_declaration_id')->references('id')->on('term_declarations')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slc_money_receipts');
    }
};
