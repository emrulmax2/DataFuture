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
        Schema::table('student_employments', function (Blueprint $table) {
            $table->string('start_date',10)->change();
            $table->string('end_date',10)->nullable()->change();

            $table->unsignedBigInteger('created_by')->change();
            $table->unsignedBigInteger('updated_by')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_employments', function (Blueprint $table) {
            $table->date('start_date')->nullable()->change();
            $table->date('end_date')->nullable()->change();

            $table->BigInteger('created_by')->change();
            $table->BigInteger('updated_by')->nullable()->change();
        });
    }
};
