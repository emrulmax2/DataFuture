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
        Schema::create('attendance_excuse_days', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_excuse_id');
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->unsignedBigInteger('plans_date_list_id')->nullable();
            $table->smallInteger('active')->default(1)->nullable();

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('attendance_excuse_id')->references('id')->on('attendance_excuses')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_excuse_days');
    }
};
