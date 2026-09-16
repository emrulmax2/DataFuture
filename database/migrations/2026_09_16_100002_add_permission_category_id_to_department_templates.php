<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Department permission templates are now set per sub department: a
 * department holds one template for each of its permission categories rather
 * than one for the department as a whole.
 *
 * Nullable for now, as agreed: rows written before categories existed carry
 * none, and making it required would fail the migration on them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('department_templates', function (Blueprint $table) {
            $table->foreignId('permission_category_id')
                ->nullable()
                ->after('department_id')
                ->constrained('permission_categories')
                ->nullOnDelete();

            $table->index(['department_id', 'permission_category_id'], 'department_templates_dept_cat_idx');
        });
    }

    public function down(): void
    {
        Schema::table('department_templates', function (Blueprint $table) {
            $table->dropIndex('department_templates_dept_cat_idx');
            $table->dropConstrainedForeignId('permission_category_id');
        });
    }
};
