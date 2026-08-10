<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            UPDATE course_base_datafutures
            SET parent_id = NULL
            WHERE parent_id = 0
               OR parent_id = id
        ");

        DB::statement("
            UPDATE course_base_datafutures child
            LEFT JOIN course_base_datafutures parent
                ON parent.id = child.parent_id
            SET child.parent_id = NULL
            WHERE child.parent_id IS NOT NULL
              AND parent.id IS NULL
        ");

        Schema::table('course_base_datafutures', function (Blueprint $table) {
            $table->index('parent_id', 'cbdf_parent_id_idx');
            $table->index(['course_id', 'parent_id'], 'cbdf_course_parent_idx');

            $table->foreign('parent_id', 'cbdf_parent_id_fk')
                ->references('id')
                ->on('course_base_datafutures')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_base_datafutures', function (Blueprint $table) {
            $table->dropForeign('cbdf_parent_id_fk');
            $table->dropIndex('cbdf_course_parent_idx');
            $table->dropIndex('cbdf_parent_id_idx');
        });
    }
};
