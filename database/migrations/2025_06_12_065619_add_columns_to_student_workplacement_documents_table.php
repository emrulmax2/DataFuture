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
        Schema::table('student_workplacement_documents', function (Blueprint $table) {
            $table->string('display_file_name')->after('path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_workplacement_documents', function (Blueprint $table) {
            $table->dropColumn('display_file_name');
        });
    }
};
