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
        Schema::create('applicant_letters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->unsignedBigInteger('letter_set_id');
            $table->unsignedBigInteger('signatory_id')->nullable();
            $table->tinyInteger('is_email_or_attachment')->default(1);
            $table->unsignedBigInteger('applicant_document_id')->nullable();
            $table->unsignedBigInteger('issued_by');
            $table->date('issued_date');

            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('applicant_letters');
    }
};
