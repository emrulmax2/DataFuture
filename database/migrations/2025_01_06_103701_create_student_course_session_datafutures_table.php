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
        Schema::create('student_course_session_datafutures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('student_course_relation_id');
            $table->unsignedBigInteger('student_stuload_information_id');
            $table->unsignedBigInteger('ELQ')->nullable();
            $table->unsignedBigInteger('FUNDCOMP')->nullable();
            $table->unsignedBigInteger('FUNDLENGTH')->nullable();
            $table->unsignedBigInteger('NONREGFEE')->nullable();
            $table->string('FINSUPTYPE')->nullable();
            $table->string('DISTANCE')->nullable();
            $table->string('STUDYPROPORTION')->nullable();

            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_course_session_datafutures');
    }
};
