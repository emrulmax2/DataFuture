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
        Schema::create('workplacement_details', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('hours');
            $table->unsignedBigInteger('course_id');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->smallInteger('active')->default(0);
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workplacement_details');
    }
};
