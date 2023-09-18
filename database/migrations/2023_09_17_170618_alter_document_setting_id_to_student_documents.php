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
        Schema::table('student_documents', function (Blueprint $table) {
            $table->dropForeign(['document_setting_id']);
            $table->bigInteger('document_setting_id')->unsigned()->nullable()->change();
            $table->foreign('document_setting_id')->references('id')->on('document_settings')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_documents', function (Blueprint $table) {
            $table->dropForeign(['document_setting_id']);
            $table->bigInteger('document_setting_id')->unsigned()->change();
            $table->foreign('document_setting_id')->references('id')->on('document_settings')->onDelete('cascade')->onUpdate('cascade');
        });
    }
};
