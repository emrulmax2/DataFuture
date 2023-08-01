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
        Schema::create('applicant_task_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_task_id');
            $table->unsignedBigInteger('applicant_document_id');

            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('applicant_task_id')->references('id')->on('applicant_tasks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicant_task_documents', function (Blueprint $table) {

            $table->dropForeign(['applicant_task_id']);
            
        });
        Schema::dropIfExists('applicant_task_documents');
    }
};
