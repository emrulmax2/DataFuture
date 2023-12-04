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
            $table->unsignedBigInteger('term_type_id')->nullable()->after('name');
            $table->foreign('term_type_id')->references('id')->on('term_types')->onDelete('set null')->onUpdate('set null');
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

            $table->dropColumn('term_type_id');
            
        });
    }
};
