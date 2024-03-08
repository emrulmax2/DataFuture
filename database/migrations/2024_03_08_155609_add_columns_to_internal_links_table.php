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
        Schema::table('internal_links', function (Blueprint $table) {
            
            $table->text('description')->nullable()->after('link');
            $table->date('start_date')->nullable()->after('description');
            $table->date('end_date')->nullable()->after('start_date');
            $table->tinyInteger('available_staff')->nullable()->after('end_date');
            $table->tinyInteger('available_student')->nullable()->after('available_staff');
            $table->tinyInteger('active')->default(1)->after('available_student')->comment('0=No, 1=Yes');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('internal_links', function (Blueprint $table) {
            //
        });
    }
};
