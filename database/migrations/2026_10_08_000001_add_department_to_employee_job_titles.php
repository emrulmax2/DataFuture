<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which department a job title belongs to.
 *
 * Nullable, because the 108 titles already in use predate the link and none of
 * them has a department yet. Forcing one would either block every edit until
 * somebody guessed, or require a bulk assignment nobody has asked for — so an
 * unassigned title stays valid and simply reads as "no department".
 *
 * Deliberately no foreign key constraint: departments are soft-deleted, and a
 * real FK would refuse the delete or cascade it. The relation is resolved in
 * the model, which is how the rest of this codebase links to departments.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_job_titles', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_job_titles', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('name');
                $table->index('department_id', 'employee_job_titles_department_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_job_titles', function (Blueprint $table) {
            $table->dropIndex('employee_job_titles_department_idx');
            $table->dropColumn('department_id');
        });
    }
};
