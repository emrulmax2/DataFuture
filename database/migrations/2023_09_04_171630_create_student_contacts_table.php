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
        Schema::create('student_contacts', function (Blueprint $table) {
            $table->id();
            $table->index('student_id');
            $table->bigInteger('student_id')->unsigned();
            $table->index('country_id');
            $table->bigInteger('country_id')->unsigned();
            $table->index('permanent_country_id');
            $table->bigInteger('permanent_country_id')->unsigned();
            $table->string('home',145)->nullable();
            $table->string('mobile',145);
            $table->text('external_link_ref')->nullable();
            $table->tinyInteger('mobile_verification')->default('0');
            $table->string('address_line_1',191);//
            $table->string('address_line_2',191)->nullable();//
            $table->string('state',145)->nullable();//
            $table->string('post_code',145);//
            $table->string('permanent_post_code',191)->nullable();
            $table->string('city',145);//
            $table->string('country',191);
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('permanent_country_id')->references('id')->on('countries')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::dropIfExists('student_contacts');
    }
};
