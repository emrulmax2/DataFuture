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
        Schema::table('student_emails', function (Blueprint $table) {
            
            $table->bigInteger('email_template_id')->unsigned()->nullable()->after("student_id");
            $table->foreign('email_template_id')->references('id')->on('email_templates')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_emails', function (Blueprint $table) {
            $table->dropForeign(['email_template_id']);
            $table->dropColumn(['email_template_id']);
        });
    }
};
