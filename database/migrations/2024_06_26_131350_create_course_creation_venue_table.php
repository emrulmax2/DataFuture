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
        Schema::create('course_creation_venue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_creation_id');
            $table->unsignedBigInteger('venue_id');
            $table->string('slc_code',191)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('course_creation_id')->references('id')->on('course_creations')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('venue_id')->references('id')->on('venues')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_creation_venue');
    }
};
