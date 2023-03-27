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
        Schema::create('instance_terms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_creation_instance_id');
            $table->string('name', 191);
            $table->tinyInteger('session_term', 4);
            $table->enum('term', ['Autumn Term', 'Spring Term', 'Summer Term', 'Winter Term']);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_teaching_weeks');
            $table->date('teaching_start_date');
            $table->date('teaching_end_date');
            $table->date('revision_start_date');
            $table->date('revision_end_date');

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('course_creation_instance_id')->references('id')->on('course_creation_instances');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('instance_terms');
    }
};
