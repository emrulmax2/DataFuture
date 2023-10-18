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
            $table->dropColumn('term_time_accommodation_type');
            $table->bigInteger('term_time_accommodation_type_id')->unsigned()->nullable()->after('term_time_address_id');
            $table->foreign('term_time_accommodation_type_id')->references('id')->on('term_time_accommodation_types')->onDelete('set null')->onUpdate('cascade');

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
            $table->dropColumn('term_time_accommodation_type');
        });
    }
};
