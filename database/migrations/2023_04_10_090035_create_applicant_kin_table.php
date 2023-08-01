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
        Schema::create('applicant_kin', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->string('name', 145);
            $table->unsignedBigInteger('kins_relation_id');
            $table->string('mobile', 145);
            $table->string('email', 145)->nullable();
            $table->string('address_line_1', 199);
            $table->string('address_line_2', 199)->nullable();
            $table->string('state', 145)->nullable();
            $table->string('post_code', 145);
            $table->string('city', 145);
            $table->string('country', 199);

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('applicant_id')->references('id')->on('applicants');
            $table->foreign('kins_relation_id')->references('id')->on('kins_relations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicant_kin', function (Blueprint $table) {
            $table->dropForeign(['applicant_id']);
            $table->dropForeign(['kins_relation_id']);
        });
        Schema::dropIfExists('applicant_kin');
    }
};
