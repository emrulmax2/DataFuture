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
        Schema::table('course_module_base_assesments', function (Blueprint $table) {
            
            $table->unsignedBigInteger('assessment_type_id')->nullable()->after('id');
            $table->tinyInteger('is_result_segment')->default(0)->after('assessment_type_id');
            $table->tinyInteger('view_in_plan')->default(0)->after('is_result_segment');
            $table->foreign('assessment_type_id')->references('id')->on('assessment_types')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course_module_base_assesments', function (Blueprint $table) {
            $table->dropForeign(['assessment_type_id']);
            $table->dropColumn(['assessment_type_id']);
        });
    }
};
