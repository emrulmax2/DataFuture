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
        Schema::create('employee_appraisals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('due_on');
            $table->date('completed_on')->nullable();
            $table->date('next_due_on')->nullable();
            $table->unsignedBigInteger('appraised_by')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->string('total_score')->nullable();
            $table->tinyInteger('promotion_consideration')->default(0)->nullable();
            $table->text('notes')->nullable();

            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('appraised_by')->references('id')->on('employees')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('reviewed_by')->references('id')->on('employees')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_appraisals');
    }
};
