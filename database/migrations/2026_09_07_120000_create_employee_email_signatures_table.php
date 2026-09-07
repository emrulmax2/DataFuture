<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-employee overrides for the London Churchill College email signature.
 *
 * The signature is always rendered from the employee's HR record first; a row
 * here only exists once the member of staff has edited something on the
 * "Email Signature" tab of My HR. Every column is therefore nullable — a NULL
 * means "keep whatever HR holds", which is what lets the signature follow a
 * job title or extension change without the staff member touching it again.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('employee_email_signatures', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id')->unsigned();

            $table->string('display_name', 191)->nullable();
            $table->string('qualifications', 191)->nullable();
            $table->string('job_title', 191)->nullable();
            $table->string('email', 191)->nullable();
            $table->string('extension', 20)->nullable();
            $table->string('mobile', 60)->nullable();

            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();

            // One signature per employee — the tab reads and writes this row.
            $table->unique(['employee_id'], 'employee_email_signatures_employee_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_email_signatures');
    }
};
