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
            $table->dropForeign(['religion_id']);
            $table->bigInteger('religion_id')->unsigned()->nullable()->change();
            $table->foreign('religion_id')->references('id')->on('religions')->onDelete('set null')->onUpdate('set null');
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
            $table->dropForeign(['religion_id']);
            $table->bigInteger('religion_id')->unsigned()->change();
            $table->foreign('religion_id')->references('id')->on('religions')->onDelete('cascade')->onUpdate('cascade');
        });
    }
};
