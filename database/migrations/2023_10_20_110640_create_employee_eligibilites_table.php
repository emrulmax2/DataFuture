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
        Schema::create('employee_eligibilites', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id')->unsigned();
            $table->string('eligible_to_work')->nullable();
            $table->string('workpermit_number')->nullable();
            $table->string('workpermit_expire')->nullable();
            $table->string('document_type')->nullable();
            $table->string('doc_number')->nullable();
            $table->string('doc_expire')->nullable();
            $table->string('doc_issue_country')->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_eligibilites');
    }
};
