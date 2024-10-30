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
        Schema::table('book_location', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->change();
            $table->foreignId('venue_id')->after('id')->nullable()->constrained('venues')->onUpdate('set null')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_location', function (Blueprint $table) {
            //
        });
    }
};
