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
        Schema::create('budget_set_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_set_id');
            $table->unsignedBigInteger('budget_name_id');
            $table->double('amount', 10, 2);

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('budget_set_id')->references('id')->on('budget_sets')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('budget_name_id')->references('id')->on('budget_names')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_set_details');
    }
};
