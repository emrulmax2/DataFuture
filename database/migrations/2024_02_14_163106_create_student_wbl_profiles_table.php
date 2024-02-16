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
        Schema::create('student_wbl_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('student_work_placement_id')->nullable();
            $table->unsignedBigInteger('company_id');

            $table->date('weif_form_provided_date')->nullable();
            $table->tinyInteger('weif_form_provided_status')->comment('0=No, 1=Yes')->nullable();
            $table->date('received_completed_weif_form_date')->nullable();
            $table->tinyInteger('received_completed_weif_form_status')->comment('0=No, 1=Yes')->nullable();
            $table->date('work_hour_update_term_date')->nullable();
            $table->tinyInteger('work_hour_update_term_status')->comment('0=No, 1=Yes')->nullable();
            $table->date('work_exp_handbook_complete_date')->nullable();
            $table->tinyInteger('work_exp_handbook_complete_status')->comment('0=No, 1=Yes')->nullable();
            $table->date('work_exp_handbook_checked_date')->nullable();
            $table->tinyInteger('work_exp_handbook_checked_status')->comment('0=No, 1=Yes')->nullable();
            $table->date('emp_handbook_sent_date')->nullable();
            $table->tinyInteger('emp_handbook_sent_status')->comment('0=No, 1=Yes')->nullable();
            $table->date('emp_letter_sent_date')->nullable();
            $table->tinyInteger('emp_letter_sent_status')->comment('0=No, 1=Yes')->nullable();
            $table->date('emp_confirm_rec_date')->nullable();
            $table->tinyInteger('emp_confirm_rec_status')->comment('0=No, 1=Yes')->nullable();
            $table->date('company_visit_date')->nullable();
            $table->tinyInteger('company_visit_status')->comment('0=No, 1=Yes')->nullable();
            $table->date('record_std_meeting_date')->nullable();
            $table->tinyInteger('record_std_meeting_status')->comment('0=No, 1=Yes')->nullable();
            $table->date('record_all_contact_student_date')->nullable();
            $table->tinyInteger('record_all_contact_student_status')->comment('0=No, 1=Yes')->nullable();
            $table->date('email_sent_emp_date')->nullable();
            $table->tinyInteger('email_sent_emp_status')->comment('0=No, 1=Yes')->nullable();
            
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('student_work_placement_id')->references('id')->on('student_work_placements')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_wbl_profiles');
    }
};
