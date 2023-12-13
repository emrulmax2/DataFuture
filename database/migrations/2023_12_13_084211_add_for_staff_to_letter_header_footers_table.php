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
        Schema::table('letter_header_footers', function (Blueprint $table) {
            $table->enum('for_staff', ['Yes', 'No'])->after('for_email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('letter_header_footers', function (Blueprint $table) {
            $table->dropColumn('for_staff');
        });
    }
};
