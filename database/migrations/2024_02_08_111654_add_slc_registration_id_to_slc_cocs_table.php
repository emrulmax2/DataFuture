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
        Schema::table('slc_cocs', function (Blueprint $table) {
            $table->unsignedBigInteger('slc_registration_id')->nullable()->after('course_creation_instance_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slc_cocs', function (Blueprint $table) {
            //
        });
    }
};
