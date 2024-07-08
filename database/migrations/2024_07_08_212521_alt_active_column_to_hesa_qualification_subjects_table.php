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
        Schema::table('hesa_qualification_subjects', function (Blueprint $table) {
            $table->smallInteger('active')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hesa_qualification_subjects', function (Blueprint $table) {
            $table->smallInteger('active')->default(0)->change();
        });
    }
};
