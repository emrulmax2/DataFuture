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
        Schema::table('student_letters', function (Blueprint $table) {
            $table->renameColumn('pin','comon_smtp_id');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_letters', function (Blueprint $table) {
            $table->renameColumn('comon_smtp_id','pin');
        });
    }
};
