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
            
            $table->bigInteger('sex_identifier')->unsigned()->nullable()->after('student_id');
            $table->foreign('sex_identifier')->references('id')->on('sex_identifiers')->onDelete('set null')->onUpdate('set null');
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
            $table->dropForeign(['sex_identifier']);
            $table->dropColumn(['sex_identifier']);
        });
    }
};
