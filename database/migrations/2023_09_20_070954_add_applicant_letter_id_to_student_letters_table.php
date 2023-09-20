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
            $table->bigInteger('applicant_letter_id')->unsigned()->nullable()->after("student_id");
            $table->foreign('applicant_letter_id')->references('id')->on('applicant_letters')->onDelete('set null')->onUpdate('set null');
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
            $table->dropForeign(['applicant_letter_id']);
            $table->dropColumn(['applicant_letter_id']);
        });
    }
};
