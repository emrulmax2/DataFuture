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
        Schema::table('student_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('term_declaration_id')->nullable()->after('student_document_id');
            $table->enum('followed_up', ['yes', 'no'])->default('no')->after('phase');
            $table->date('follow_up_start')->nullable()->after('followed_up');
            $table->date('follow_up_end')->nullable()->after('follow_up_start');
            $table->unsignedBigInteger('follow_up_by')->nullable()->after('follow_up_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_notes', function (Blueprint $table) {
            //
        });
    }
};
