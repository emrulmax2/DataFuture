<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_sms', function (Blueprint $table) {
            //$table->dropForeign('sms_template_id');
            $table->dropColumn(['subject', 'sms', 'sms_template_id']);
            $table->unsignedBigInteger('student_sms_content_id')->after('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_sms', function (Blueprint $table) {
            $table->dropColumn('student_sms_content_id');
            $table->text('subject');
            $table->text('sms');
            $table->bigInteger('sms_template_id')->unsigned()->nullable()->after("student_id");
            $table->foreign('sms_template_id')->references('id')->on('sms_templates')->onDelete('set null')->onUpdate('set null');
        });
    }
};
