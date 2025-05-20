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
        Schema::create('level_hours', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('hours');
            $table->unsignedBigInteger('workplacement_details_id');
            $table->smallInteger('active')->default(0);
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('workplacement_details_id')->references('id')->on('workplacement_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('level_hours');
    }
};
