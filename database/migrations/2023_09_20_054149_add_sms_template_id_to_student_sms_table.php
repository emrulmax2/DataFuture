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
        Schema::table('student_sms', function (Blueprint $table) {
            $table->bigInteger('sms_template_id')->unsigned()->nullable()->after("student_id");
            $table->foreign('sms_template_id')->references('id')->on('sms_templates')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_sms', function (Blueprint $table) {
            $table->dropForeign(['sms_template_id']);
            $table->dropColumn(['sms_template_id']);
        });
    }
};
