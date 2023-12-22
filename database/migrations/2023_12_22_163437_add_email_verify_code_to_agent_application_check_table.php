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
        Schema::table('agent_application_checks', function (Blueprint $table) {
            
            $table->string("email_verify_code")->after('verify_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agent_application_checks', function (Blueprint $table) {

            $table->dropColumn("email_verify_code");
            
        });
    }
};
