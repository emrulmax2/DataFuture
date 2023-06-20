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
        Schema::table('applicant_contacts', function (Blueprint $table) {
            $table->tinyInteger('mobile_verification')->nullable()->after('mobile')->default('0');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicant_contacts', function (Blueprint $table) {
            $table->dropColumn('mobile_verification');
        });
    }
};
