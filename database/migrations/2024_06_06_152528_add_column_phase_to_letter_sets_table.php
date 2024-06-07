<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('letter_sets', function (Blueprint $table) {
            $table->smallInteger('admission')->default(0)->after('description');
            $table->smallInteger('live')->default(0)->after('admission');
            $table->smallInteger('hr')->default(0)->after('live');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letter_sets', function (Blueprint $table) {
            //
        });
    }
};
