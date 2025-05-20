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
        Schema::table('student_work_placements', function (Blueprint $table) {
            $table->unsignedBigInteger('workplacement_details_id')->nullable()->after('student_id');
            $table->unsignedBigInteger('level_hours_id')->nullable()->after('workplacement_details_id');
            $table->unsignedBigInteger('learning_hours_id')->nullable()->after('level_hours_id');
            $table->unsignedBigInteger('workplacement_setting_id')->nullable()->after('learning_hours_id');
            $table->unsignedBigInteger('workplacement_setting_type_id')->nullable()->after('workplacement_setting_id');
            $table->unsignedBigInteger('assign_module_list_id')->nullable()->after('workplacement_setting_type_id');
            $table->enum('status', ['Pending', 'Rejected', 'Confirmed'])->default('Pending')->nullable()->after('workplacement_setting_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_work_placements', function (Blueprint $table) {
            $table->dropColumn([
                'assign_module_list_id',
                'learning_hours_id',
                'level_hours_id',
                'workplacement_details_id',
                'workplacement_setting_id',
                'workplacement_setting_type_id',
                'status'
            ]);
        });
    }
};