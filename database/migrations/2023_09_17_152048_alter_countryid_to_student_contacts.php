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
        Schema::table('student_contacts', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->bigInteger('country_id')->unsigned()->nullable()->change();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null')->onUpdate('set null');

            $table->dropForeign(['permanent_country_id']);
            $table->bigInteger('permanent_country_id')->unsigned()->nullable()->change();
            $table->foreign('permanent_country_id')->references('id')->on('countries')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_contacts', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->bigInteger('country_id')->unsigned()->change();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade')->onUpdate('cascade');

            $table->dropForeign(['permanent_country_id']);
            $table->bigInteger('permanent_country_id')->unsigned()->change();
            $table->foreign('permanent_country_id')->references('id')->on('countries')->onDelete('cascade')->onUpdate('cascade');
        });
    }
};
