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
            $table->unsignedBigInteger('term_declaration_id')->nullable()->after('plan_id');
            $table->foreign('term_declaration_id')->references('id')->on('term_declarations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_visits', function (Blueprint $table) {
            //
            $table->dropForeign(['term_declaration_id']);
            $table->dropColumn('term_declaration_id');
        });
    }
};
