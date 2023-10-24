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
        Schema::table('employee_eligibilites', function (Blueprint $table) {
            $table->bigInteger('document_type')->unsigned()->nullable()->change();
            $table->foreign('document_type')->references('id')->on('employee_work_document_types')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_eligibilites', function (Blueprint $table) {
            $table->dropForeign('employee_eligibilites_document_type_foreign');
            //rollback unnecessary
        });
    }
};
