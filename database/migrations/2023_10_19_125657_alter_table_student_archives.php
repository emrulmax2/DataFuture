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
        Schema::table('student_archives', function (Blueprint $table) {
            $table->bigInteger('created_by')->unsigned()->nullable()->change();
            $table->bigInteger('student_user_id')->unsigned()->after('field_new_value')->nullable();
            $table->foreign('student_user_id')->references('id')->on('student_users')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_archives', function (Blueprint $table) {
            $table->bigInteger('created_by')->unsigned()->nullable(false)->change();
            $table->dropForeign('student_archives_student_user_id_foreign');
            $table->dropColumn('student_user_id');
        });
    }
};
