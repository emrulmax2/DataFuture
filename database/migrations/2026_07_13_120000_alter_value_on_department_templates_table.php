<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Not every permission is a checkbox. The accounts type select stores 1/2/3
     * and temporary remote access stores a date range, neither of which fits a
     * tinyInteger. employee_permissions.value is already a string; this brings
     * the department templates in line with it.
     */
    public function up(): void
    {
        Schema::table('department_templates', function (Blueprint $table) {
            $table->string('value')->default('0')->change();
        });
    }

    public function down(): void
    {
        Schema::table('department_templates', function (Blueprint $table) {
            $table->tinyInteger('value')->default(0)->change();
        });
    }
};
