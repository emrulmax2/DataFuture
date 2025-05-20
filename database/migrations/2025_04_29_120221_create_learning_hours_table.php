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
        Schema::create('learning_hours', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('hours');
            $table->unsignedBigInteger('level_hours_id');
            $table->smallInteger('active')->default(0);
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('level_hours_id')->references('id')->on('level_hours')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_hours');
    }
};
