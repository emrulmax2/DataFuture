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
        Schema::table('applicant_letters', function (Blueprint $table) {
            
            $table->bigInteger('comon_smtp_id')->unsigned()->nullable()->change();
            $table->foreign('comon_smtp_id')->references('id')->on('comon_smtps')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicant_letters', function (Blueprint $table) {
            $table->dropForeign(['comon_smtp_id']);
            $table->bigInteger('comon_smtp_id')->unsigned()->change();
        });
    }
};
