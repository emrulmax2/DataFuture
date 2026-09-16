<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Employee permissions already record which department's template they were
 * loaded from (department_id), so the HR privilege screen can preselect it.
 * Templates are now per sub department, so the category is recorded beside it —
 * otherwise reopening an employee would preselect the department and leave the
 * sub department picker blank.
 *
 * Nullable: every existing row predates categories, and permissions set
 * directly rather than from a template have neither.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_permissions', function (Blueprint $table) {
            $table->foreignId('permission_category_id')
                ->nullable()
                ->after('department_id')
                ->constrained('permission_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employee_permissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('permission_category_id');
        });
    }
};
