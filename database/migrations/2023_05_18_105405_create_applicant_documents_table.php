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
        Schema::create('applicant_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->unsignedBigInteger('document_setting_id')->nullable();
            $table->tinyInteger('hard_copy_check')->nullable();
            $table->string('doc_type', 145)->nullable();
            $table->string('disk_type', 145)->nullable();
            $table->string('path', 191);
            $table->string('display_file_name', 191);
            $table->string('current_file_name', 191);
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('applicant_id')->references('id')->on('applicants');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicant_documents', function (Blueprint $table) {

            $table->dropForeign(['applicant_id']);
            
        });
        Schema::dropIfExists('applicant_documents');
    }
};
