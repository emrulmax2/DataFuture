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
        Schema::table('student_document_request_forms', function (Blueprint $table) {
            $table->tinyInteger('letter_generated_count')->default(0)->after('letter_set_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_document_request_forms', function (Blueprint $table) {
            $table->dropColumn('letter_generated_count');
        });
    }
};
