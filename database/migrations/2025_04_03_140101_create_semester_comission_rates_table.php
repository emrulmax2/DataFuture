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
        Schema::create('semester_comission_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->nullable()->index()->constrained('semesters', 'id')->cascadeOnDelete();
            $table->double('rate', 10, 2)->nullable()->default(0);

            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semester_comission_rates');
    }
};
