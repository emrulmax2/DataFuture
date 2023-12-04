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
        Schema::table('term_declarations', function (Blueprint $table) {

            $table->unsignedBigInteger('academic_year_id')->nullable()->after('term_type_id');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('set null')->onUpdate('set null');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('term_declarations', function (Blueprint $table) {

            $table->dropColumn('academic_year_id');

        });
    }
};
