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
            $table->foreignId('student_order_id')
            ->nullable()
            ->after('student_id')
            ->constrained('student_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_document_request_forms', function (Blueprint $table) {
            $table->dropForeign(['student_order_id']);
            $table->dropColumn('student_order_id');
        });
    }
};
