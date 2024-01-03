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
        Schema::create('mobile_verification_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('applicant_id')->nullable();
            $table->unsignedInteger('student_id')->nullable();
            $table->string('mobile', 191);
            $table->string('code', 191);
            $table->tinyInteger('status')->default(0);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mobile_verification_codes');
    }
};
