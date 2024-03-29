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
        Schema::table('student_other_details', function (Blueprint $table) {
            $table->dropForeign('student_other_details_sex_identifier_foreign');
            $table->dropColumn('sex_identifier');
            $table->dropColumn('gender_identity');
            $table->bigInteger('hesa_gender_id')->unsigned()->after('college_introduction')->nullable();
            $table->foreign('hesa_gender_id')->references('id')->on('hesa_genders')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_other_details', function (Blueprint $table) {
            $table->dropForeign('student_other_details_hesa_gender_id_foreign');
            $table->enum('gender_identity',['Yes','No','Refused'])->after('hesa_gender_id');
            
            $table->dropColumn('hesa_gender_id');
            
            $table->bigInteger('sex_identifier')->unsigned()->after('sexual_orientation_id')->nullable();
            $table->foreign('sex_identifier')->references('id')->on('sex_identifiers')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }
};
