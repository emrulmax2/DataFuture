<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permissions can now be set on an employee directly, without loading a
     * department template first. department_id records which template was used,
     * so "no template" has to be expressible as null rather than a fake id.
     */
    public function up(): void
    {
        Schema::table('employee_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('employee_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable(false)->change();
        });
    }
};
