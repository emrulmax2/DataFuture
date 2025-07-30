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
        Schema::table('student_visits', function (Blueprint $table) {
            $table->unsignedBigInteger('module_creation_id')->nullable()->after('id');
            $table->unsignedBigInteger('term_declaration_id')->nullable()->after('module_creation_id');
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_visits', function (Blueprint $table) {
            $table->dropColumn('module_creation_id');
            $table->dropColumn('term_declaration_id');
        });
    }
};
