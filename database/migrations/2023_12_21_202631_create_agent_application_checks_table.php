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
        Schema::create('agent_application_checks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_user_id')->nullable();
            $table->unsignedBigInteger('applicant_id')->nullable();
            $table->string("first_name");
            $table->string("last_name");
            $table->string("mobile");
            $table->string("email");
            $table->string("verify_code");
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('mobile_verified_at')->nullable();
            $table->integer('active')->default("0");
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('agent_user_id')->references('id')->on('agent_users')->onDelete('set null')->onUpdate('set null');
            $table->foreign('applicant_id')->references('id')->on('applicants')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agent_application_checks');
    }
};
