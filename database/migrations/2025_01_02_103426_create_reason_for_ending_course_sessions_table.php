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
        Schema::create('reason_for_ending_course_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->tinyInteger('is_hesa')->default(0);
            $table->string('hesa_code', 99)->nullable();
            $table->tinyInteger('is_df')->default(0);
            $table->string('df_code', 99)->nullable();
            $table->smallInteger('active')->default(0);

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
        Schema::dropIfExists('reason_form_ending_course_sessions');
    }
};
