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
        Schema::create('student_stuload_information', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('student_course_relation_id');
            $table->unsignedBigInteger('course_creation_instance_id');
            $table->smallInteger('year_of_the_course')->nullable()->default(0);
            $table->smallInteger('auto_stuload')->nullable()->default(1); // 1
            $table->integer('student_load')->nullable()->default(0); // A == 33 
            $table->unsignedBigInteger('disall_id')->nullable();//disabilities
            $table->unsignedBigInteger('exchind_id')->nullable();//Not applicable
            $table->double('gross_fee', 10, 2)->nullable();//Instance Fee
            $table->unsignedBigInteger('locsdy_id')->nullable();//Not Found
            $table->unsignedBigInteger('mode_id')->nullable();// DEFAULT 01
            $table->unsignedBigInteger('mstufee_id')->nullable();//Not Found
            $table->double('netfee', 10, 2)->nullable();//Instance Fee
            $table->integer('notact_id')->nullable(); //Not Found
            $table->date('periodstart')->nullable(); // Instance Start
            $table->date('periodend')->nullable(); //Instance End
            $table->unsignedBigInteger('priprov_id')->nullable(); //Last Education Qual Provider Name
            $table->unsignedBigInteger('sselig_id')->nullable(); //Note Now
            $table->string('yearprg')->nullable(); // Stuload Count
            $table->string('yearstu')->nullable(); // Stuload Count
            $table->unsignedBigInteger('qual_id')->nullable(); //Not Found -- Update from dropdown 
            $table->smallInteger('heapespop_id')->nullable()->default(0); // Not Needed
            $table->unsignedBigInteger('class_id')->nullable();// Results -> Overall Result:
            $table->unsignedBigInteger('courseaim_id')->nullable(); // Course ID
            $table->unsignedBigInteger('genderid_id')->nullable(); // Gender
            $table->unsignedBigInteger('regbody_id')->nullable(); // Note Needed
            $table->unsignedBigInteger('relblf_id')->nullable();// Religion Belief id
            $table->unsignedBigInteger('rsnend_id')->nullable();// Update from dropdown 
            $table->unsignedBigInteger('sexort_id')->nullable();// Sexual Oriantation
            $table->unsignedBigInteger('ttcid_id')->nullable();// term type accomodation type
            $table->string('uhn_number')->nullable(); // Std UHN Numb
            $table->string('sid_number')->nullable(); // Calculation
            $table->unsignedBigInteger('provider_name')->nullable(); //Last Education Qual Provider Name
            $table->unsignedBigInteger('qual_type')->nullable(); //Last Education Qual Provider Name
            $table->unsignedBigInteger('qual_sub')->nullable(); //Last Education Qual Provider Name
            $table->unsignedBigInteger('qual_sit')->nullable(); //Last Education Qual Provider Name
            $table->unsignedBigInteger('domicile_id')->nullable(); //Permanent country of code :  -- Update from dropdown 
            $table->integer('numhus')->nullable()->default(1); // Not Needed
            $table->string('owninst')->nullable();//LCCID
            $table->date('comdate')->nullable();// student_course_relations Start
            $table->date('enddate')->nullable();// student_course_relations End
            $table->unsignedBigInteger('qualent3_id')->nullable(); //Last Education Qual Provider Name
            $table->smallInteger('reporting_period')->nullable()->default(0);//Default set 0. 

            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('student_course_relation_id', 'ssi_scri_fk')->references('id')->on('student_course_relations')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('course_creation_instance_id', 'ssi_ccii_fk')->references('id')->on('course_creation_instances')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_stuload_information');
    }
};
