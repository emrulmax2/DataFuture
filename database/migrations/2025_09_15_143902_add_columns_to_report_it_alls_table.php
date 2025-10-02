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
        Schema::table('report_it_alls', function (Blueprint $table) {
            $table->unsignedBigInteger('venue_id')->nullable()->after('description');
            $table->foreign('venue_id')->references('id')->on('venues')->onDelete('set null');
            $table->string('location')->nullable()->after('venue_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_it_alls', function (Blueprint $table) {
            $table->dropForeign(['venue_id']);
            $table->dropColumn('venue_id');
            $table->dropColumn('location');
        });
    }
};
