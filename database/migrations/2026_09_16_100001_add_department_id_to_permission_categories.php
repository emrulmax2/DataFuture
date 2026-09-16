<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A permission category now belongs to a department, and its own name is the
 * sub-department within it.
 *
 * Nullable at the database level on purpose: rows created before this change
 * have no department, and a NOT NULL column would fail the migration on them.
 * The form requires one for every new or edited category, so the column fills
 * in as categories are touched rather than all at once with a guessed value.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permission_categories', function (Blueprint $table) {
            $table->foreignId('department_id')
                ->nullable()
                ->after('id')
                ->constrained('departments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('permission_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
        });
    }
};
