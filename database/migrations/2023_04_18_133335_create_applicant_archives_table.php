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
        Schema::create('applicant_archives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->string('table', 145)->nullable();
            $table->string('field_name', 145)->nullable();
            $table->text('field_value')->nullable();
            $table->text('field_new_value')->nullable();

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
        Schema::table('applicant_archives', function (Blueprint $table) {

            $table->dropForeign(['applicant_id']);

        });
        Schema::dropIfExists('applicant_archives');
    }
};
