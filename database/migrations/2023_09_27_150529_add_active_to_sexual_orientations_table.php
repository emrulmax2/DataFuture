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
        Schema::table('sexual_orientations', function (Blueprint $table) {
            $table->tinyInteger('active')->default(0)->after('df_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sexual_orientations', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
};
