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
            $table->date('doc_expire')->nullable()->change();
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
            $table->string('doc_expire', 191)->nullable(false)->change();
        });
    }
};
