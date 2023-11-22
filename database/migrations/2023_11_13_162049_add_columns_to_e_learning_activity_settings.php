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
        Schema::table('e_learning_activity_settings', function (Blueprint $table) {
            //'days_reminder',
            //'is_mandatory',
            $table->string('days_reminder')->nullable()->after('has_week');
            $table->string('is_mandatory')->nullable()->after('days_reminder');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('e_learning_activity_settings', function (Blueprint $table) {
            //
            $table->dropColumn('days_reminder');
            $table->dropColumn('is_mandatory');
        });
    }
};
