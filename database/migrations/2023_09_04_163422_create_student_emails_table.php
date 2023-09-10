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
        Schema::create('student_emails', function (Blueprint $table) {
            $table->id();
            $table->index('student_id');
            $table->bigInteger('student_id')->unsigned();
            $table->index('common_smtp_id');
            $table->bigInteger('common_smtp_id')->unsigned();
            $table->string('subject',191);
            $table->longText('body');
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('common_smtp_id')->references('id')->on('comon_smtps')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::dropIfExists('student_emails');
    }
};
