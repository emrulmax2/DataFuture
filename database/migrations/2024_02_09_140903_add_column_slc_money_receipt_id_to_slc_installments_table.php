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
        Schema::table('slc_installments', function (Blueprint $table) {
            $table->unsignedBigInteger('slc_money_receipt_id')->after('term_declaration_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slc_installments', function (Blueprint $table) {
            //
        });
    }
};
