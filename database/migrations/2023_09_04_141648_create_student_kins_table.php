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
        Schema::create('student_kins', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('student_id')->unsigned();
            $table->bigInteger('kins_relation_id')->unsigned();
            $table->string('name',145)->nullable();
            $table->string('mobile',191);
            $table->string('email',145)->nullable();
            $table->string('address_line_1',191);
            $table->string('address_line_2',191)->nullable();
            $table->string('state',191)->nullable();
            $table->string('post_code',145);
            $table->string('city',191)->nullable();
            $table->string('country',191);
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('kins_relation_id')->references('id')->on('kins_relations')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_kins');
    }
};
