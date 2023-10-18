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
        Schema::table('student_letters', function (Blueprint $table) {
            $table->dropForeign(['student_document_id']);
            $table->bigInteger('student_document_id')->unsigned()->nullable()->change();
            $table->foreign('student_document_id')->references('id')->on('student_documents')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_letters', function (Blueprint $table) {

            $table->dropForeign(['student_document_id']);
            $table->bigInteger('student_document_id')->unsigned()->change();
            $table->foreign('student_document_id')->references('id')->on('student_documents')->onDelete('cascade')->onUpdate('cascade');
        });
    }
};
