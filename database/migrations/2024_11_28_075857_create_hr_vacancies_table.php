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
        Schema::create('hr_vacancies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hr_vacancy_type_id')->nullable();
            $table->string('title');
            $table->text('link')->nullable();
            $table->date('date')->nullable();
            $table->string('document')->nullable();
            $table->smallInteger('active')->default(1)->comment('0=Inactive, 1=Active');

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('hr_vacancy_type_id')->references('id')->on('hr_vacancy_types')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_vacancies');
    }
};
