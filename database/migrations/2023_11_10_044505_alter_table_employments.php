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
        Schema::table('employments', function (Blueprint $table) {
            $table->integer('last_action')->after('email')->nullable();
            $table->date('last_action_date')->after('last_action')->nullable();
            $table->time('last_action_time')->after('last_action_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employments', function (Blueprint $table) {
            $table->dropColumn('last_action');
            $table->dropColumn('last_action_date');
            $table->dropColumn('last_action_time');
        });
    }
};
