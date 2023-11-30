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
        Schema::table('course_creations', function (Blueprint $table) {
            $table->unsignedBigInteger('venue_id')->nullable()->after('course_creation_qualification_id');
            $table->decimal('fees', 10, 2)->nullable()->after('slc_code');
            $table->decimal('reg_fees', 10, 2)->nullable()->after('fees');

            $table->foreign('venue_id')->references('id')->on('venues')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course_creations', function (Blueprint $table) {
            $table->dropForeign('venue_id');
            $table->dropColumn('venue_id');
            $table->dropColumn('fees');
            $table->dropColumn('reg_fees');
        });
    }
};
