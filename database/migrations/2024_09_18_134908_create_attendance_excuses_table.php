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
        Schema::create('attendance_excuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->text('reason')->nullable();
            $table->text('remarks')->nullable();
            $table->smallInteger('status')->default(0)->comment('0 = Pending, 1 = Reviewed & Rejected, 2 = Reviewed & Approved')->nullable();
            $table->enum('attendance_types', ['E', 'M', 'H'])->nullable();

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_excuses');
    }
};
