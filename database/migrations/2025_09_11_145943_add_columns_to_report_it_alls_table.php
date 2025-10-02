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
        Schema::table('report_it_alls', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->foreignId('employee_id')->nullable()->after('student_user_id')->constrained('employees')->onDelete('set null');
            $table->foreignId('student_id')->nullable()->after('employee_id')->constrained('students')->onDelete('set null');
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->dropForeign(['student_user_id']);
            $table->dropColumn('student_user_id');
            $table->enum('status', ['Pending', 'In Progress', 'Resolved'])->default('Pending')->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_it_alls', function (Blueprint $table) {
            //
        });
    }
};
