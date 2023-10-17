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
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn('gender');
            $table->bigInteger('sex_identifier_id')->unsigned()->after('date_of_birth');
            $table->foreign('sex_identifier_id')->references('id')->on('sex_identifiers')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn('gender');
            $table->dropColumn('sex_identifier_id');
        });
    }
};
