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
        Schema::create('applicant_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('permanent_country_id')->nullable();
            $table->string('home', 145)->nullable();
            $table->string('mobile', 145);
            $table->string('address_line_1', 199);
            $table->string('address_line_2', 199)->nullable();
            $table->string('state', 145)->nullable();
            $table->string('post_code', 145);
            $table->string('permanent_post_code', 191)->nullable();
            $table->string('city', 145);
            $table->string('country', 199);


            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('applicant_id')->references('id')->on('applicants');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicant_contacts', function (Blueprint $table) {
            $table->dropForeign(['applicant_id']);
        });
        Schema::dropIfExists('applicant_contacts');
    }
};
