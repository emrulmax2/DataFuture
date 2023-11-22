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
        Schema::table('attendances', function (Blueprint $table) {
            $table->bigInteger('plans_date_list_id')->unsigned()->after('id');
            $table->bigInteger('student_id')->unsigned()->after('plans_date_list_id');
            $table->bigInteger('attendance_feed_status_id')->unsigned()->after('student_id');
            $table->tinyInteger('email_notification')->default(0)->after('attendance_feed_status_id');
            $table->tinyInteger('sms_notification')->default(0)->after('email_notification');
            $table->bigInteger('created_by')->unsigned()->after('sms_notification');
            $table->bigInteger('updated_by')->unsigned()->nullable()->after('created_by');
            $table->softDeletes();

            $table->foreign('plans_date_list_id', 'fkplans_date_id')->references('id')->on('plans_date_lists')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('student_id','attendances_student_foreign')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('attendance_feed_status_id','fkfeed_status')->references('id')->on('attendance_feed_statuses')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('created_by','attendances_user_foreign')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign('fkplans_date_id');
            $table->dropForeign('attendances_student_foreign');
            $table->dropForeign('fkfeed_status');
            $table->dropForeign('attendances_user_foreign');
            $table->dropColumn('plans_date_list_id');
            $table->dropColumn('student_id');
            $table->dropColumn('attendance_feed_status_id');
            $table->dropColumn('email_notification');
            $table->dropColumn('sms_notification');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
            $table->dropColumn('deleted_at');
        });
    }
};
