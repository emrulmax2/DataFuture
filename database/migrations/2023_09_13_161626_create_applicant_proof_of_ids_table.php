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
        Schema::create('applicant_proof_of_ids', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->enum('proof_type', ['passport','birth','driving','nid','respermit'])->nullable();
            $table->string('proof_id', 100)->nullable();
            $table->date('proof_expiredate')->nullable();

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
        Schema::table('applicant_proof_of_ids', function (Blueprint $table) {
            $table->dropForeign(['applicant_id']);
        });

        Schema::dropIfExists('applicant_proof_of_ids');
    }
};
