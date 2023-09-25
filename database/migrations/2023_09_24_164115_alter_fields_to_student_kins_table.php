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
        Schema::table('student_kins', function (Blueprint $table) {
            $table->dropColumn('address_line_1');
            $table->dropColumn('address_line_2');
            $table->dropColumn('state');
            $table->dropColumn('post_code');
            $table->dropColumn('city');
            $table->dropColumn('country');
            $table->bigInteger('address_id')->unsigned()->nullable()->after("kins_relation_id");
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_kins', function (Blueprint $table) {
            $table->string('address_line_1',191);
            $table->string('address_line_2',191)->nullable();
            $table->string('state',145)->nullable();
            $table->string('post_code',145);
            $table->string('city',191)->nullable();
            $table->string('country',191);
            $table->dropForeign(['address_id']);
            $table->dropColumn(['address_id']);
        });
    }
};
