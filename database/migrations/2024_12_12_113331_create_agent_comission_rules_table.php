<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agent_comission_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_user_id');
            $table->unsignedBigInteger('semester_id');
            $table->string('code', 50)->nullable();
            $table->smallInteger('comission_mode')->nullable()->comment('1=Percentage, 2=Fixed Amount');
            $table->integer('percentage')->nullable();
            $table->double('amount', 10, 2)->nullable();
            $table->smallInteger('period')->nullable()->comment('1=Full Course, 2=Yearly');
            $table->smallInteger('payment_type')->nullable()->comment('1=Single Payment, 2=On Receipt');

            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('agent_user_id')->references('id')->on('agent_users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_comission_rules');
    }
};
