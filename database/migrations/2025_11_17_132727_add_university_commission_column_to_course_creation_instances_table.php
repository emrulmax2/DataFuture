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
        Schema::table('course_creation_instances', function (Blueprint $table) {
            $table->double('university_commission', 10, 2)->nullable()->after('reg_fees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_creation_instances', function (Blueprint $table) {
            //
        });
    }
};
