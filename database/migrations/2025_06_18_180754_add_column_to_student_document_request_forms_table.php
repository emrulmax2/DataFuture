<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        DB::statement("ALTER TABLE student_document_request_forms MODIFY COLUMN service_type ENUM('Same Day (cost £10.00)', '3 Working Days (Free)', '3 Working Days (cost £10.00)') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_document_request_forms', function (Blueprint $table) {
            DB::statement("ALTER TABLE student_document_request_forms MODIFY COLUMN service_type ENUM('Same Day (cost £10.00)', '3 Working Days (Free)') NOT NULL");
        });
    }
};
