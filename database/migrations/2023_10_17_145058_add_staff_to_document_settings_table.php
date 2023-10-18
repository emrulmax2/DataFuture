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
        Schema::table('document_settings', function (Blueprint $table) {
            //
            $table->enum('staff',[0,1])->default(0)->after('live');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_settings', function (Blueprint $table) {
            $table->dropColumn('staff');
        });
    }
};
