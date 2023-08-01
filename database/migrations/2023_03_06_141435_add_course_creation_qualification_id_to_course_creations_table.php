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
        Schema::table('course_creations', function (Blueprint $table) {
            $table->unsignedBigInteger('course_creation_qualification_id')->nullable()->after('course_id');

            $table->foreign('course_creation_qualification_id')->references('id')
                ->on('course_creation_qualifications')
                ->onUpdate('cascade')
                ->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course_creations', function (Blueprint $table) {
            $table->dropForeign(['course_creation_qualification_id']);
            
            $table->dropColumn('course_creation_qualification_id');
        });
    }
};
