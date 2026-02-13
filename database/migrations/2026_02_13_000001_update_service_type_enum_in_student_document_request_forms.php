<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `student_document_request_forms`
            MODIFY COLUMN `service_type` ENUM(
                'Same Day (cost £10.00)',
                '3 Working Days (Free)',
                '3 Working Days (cost £10.00)',
                'Printer Top Up (cost £5.00)'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE `student_document_request_forms`
            MODIFY COLUMN `service_type` ENUM(
                'Same Day (cost £10.00)',
                '3 Working Days (Free)',
                '3 Working Days (cost £10.00)'
            ) NOT NULL
        ");
    }
};