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
            
            $table->dropForeign(['sexual_orientation_id']);
            $table->bigInteger('sexual_orientation_id')->unsigned()->nullable()->change();
            $table->foreign('sexual_orientation_id')->references('id')->on('sexual_orientations')->onDelete('set null')->onUpdate('set null');
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
            $table->dropForeign(['sexual_orientation_id']);
            $table->bigInteger('sexual_orientation_id')->unsigned()->change();
            $table->foreign('sexual_orientation_id')->references('id')->on('sexual_orientations')->onDelete('cascade')->onUpdate('cascade');
        });
    }
};
