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
        Schema::table('process_lists', function (Blueprint $table) {
            $table->enum('auto_feed', ['Yes', 'No'])->default('No')->after('phase');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('process_lists', function (Blueprint $table) {
            $table->dropColumn('auto_feed');
        });
    }
};
