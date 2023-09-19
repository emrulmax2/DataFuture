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
        Schema::table('applicant_proposed_courses', function (Blueprint $table) {
            $table->bigInteger('academic_year_id')->unsigned()->nullable()->after('semester_id');

            $table->foreign('academic_year_id')->references('id')->on('academic_years');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicant_proposed_courses', function (Blueprint $table) {
            $table->dropColumn('academic_year_id');
            $table->dropForeign(['academic_year_id']);
        });
    }
};
