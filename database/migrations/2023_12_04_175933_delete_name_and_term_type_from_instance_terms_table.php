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
        Schema::table('instance_terms', function (Blueprint $table) {
            //
            $table->dropColumn('name');
            $table->dropColumn('term');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('instance_terms', function (Blueprint $table) {
            //
            $table->string('name',191)->nullable()->after('course_creation_instance_id');
            
            $table->enum('term', ['Autumn Term', 'Spring Term', 'Summer Term','Winter Term'])->default('Winter Term');
        });
    }
};
