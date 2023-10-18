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
        Schema::table('student_awarding_body_details', function (Blueprint $table) {
            $table->bigInteger('student_course_relation_id')->unsigned()->after('id');
            $table->foreign('student_course_relation_id')->references('id')->on('student_course_relations')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_awarding_body_details', function (Blueprint $table) {
            $table->dropColumn('student_course_relation_id');
        });
    }
};
