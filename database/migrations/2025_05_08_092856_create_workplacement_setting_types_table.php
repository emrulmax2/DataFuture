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
        Schema::create('workplacement_setting_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workplacement_setting_id');
            $table->string('type');
            $table->smallInteger('active')->default(0);
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('workplacement_setting_id')->references('id')->on('workplacement_settings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workplacement_setting_types');
    }
};
