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
        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_year_id')->nullable()->after('id');
            $table->unsignedBigInteger('course_creation_id')->nullable()->after('academic_year_id');;
            $table->unsignedBigInteger('instance_term_id')->nullable()->after('course_creation_id');;

            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('set null')->onUpdate('set null');
            $table->foreign('course_creation_id')->references('id')->on('course_creations')->onDelete('set null')->onUpdate('set null');
            $table->foreign('instance_term_id')->references('id')->on('instance_terms')->onDelete('set null')->onUpdate('set null');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('academic_year_id');
            $table->dropColumn('course_creation_id');
            $table->dropColumn('instance_term_id');
        });
    }
};
