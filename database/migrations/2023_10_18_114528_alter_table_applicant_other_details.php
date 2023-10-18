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
        Schema::table('applicant_other_details', function (Blueprint $table) {
            $table->dropColumn('gender_identity');
            $table->bigInteger('hesa_gender_id')->unsigned()->after('college_introduction')->nullable();
            $table->foreign('hesa_gender_id')->references('id')->on('hesa_genders')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
