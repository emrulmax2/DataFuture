<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('course_creation_instances', function (Blueprint $table) {
            $table->decimal('fees', 10, 2)->nullable()->after('total_teaching_week');
            $table->decimal('reg_fees', 10, 2)->nullable()->after('fees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course_creation_instances', function (Blueprint $table) {
            $table->dropColumn('fees');
            $table->dropColumn('reg_fees');
        });
    }
};
