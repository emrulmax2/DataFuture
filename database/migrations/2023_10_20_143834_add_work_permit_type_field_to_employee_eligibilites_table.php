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
            $table->bigInteger('employee_work_permit_type_id')->unsigned()->nullable()->after('eligible_to_work');
           
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
            //
            $table->dropColumn('employee_work_permit_type_id');
        });
    }
};
