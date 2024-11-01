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
        Schema::table('library_books', function (Blueprint $table) {
            $table->unsignedBigInteger('book_location_id')->nullable()->change();
            $table->unsignedBigInteger('amazon_book_information_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('library_books', function (Blueprint $table) {
            //
        });
    }
};
