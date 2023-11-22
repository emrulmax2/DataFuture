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
        Schema::table('permission_templates', function (Blueprint $table) {
            $table->bigInteger('department_id')->unsigned()->nullable()->change();
            $table->dropColumn('type');
            $table->dropColumn('R');
            $table->dropColumn('W');
            $table->dropColumn('D');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permission_templates', function (Blueprint $table) {
            $table->bigInteger('department_id')->unsigned()->nullable(false)->change();
            $table->string('type', 191);
            $table->enum('R', ['0', '1'])->default('0');
            $table->enum('W', ['0', '1'])->default('0');
            $table->enum('D', ['0', '1'])->default('0');
        });
    }
};
