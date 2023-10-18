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
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('gender');
            $table->bigInteger('sex_identifier_id')->unsigned()->after('marital_status')->nullable();
            $table->foreign('sex_identifier_id')->references('id')->on('sex_identifiers')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('students', function (Blueprint $table) {

            $table->enum('gender',['MALE','FEMALE','OTHERS'])->after('date_of_birth');
            $table->dropForeign('students_sex_identifier_id_foreign');
            $table->dropColumn('sex_identifier_id');
        });
    }
};
