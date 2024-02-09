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
        Schema::table('slc_attendances', function (Blueprint $table) {
            $table->dropColumn('attendance_term');
            $table->unsignedBigInteger('term_declaration_id')->after('attendance_year')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slc_attendances', function (Blueprint $table) {
            $table->dropColumn('term_declaration_id');
            $table->unsignedBigInteger('attendance_term')->after('attendance_year')->nullable();
        });
    }
};
