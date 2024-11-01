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
        Schema::table('amazon_book_informations', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->change();
            $table->string('author', 191)->change()->nullable();
            $table->string('publisher', 191)->change()->nullable();
            $table->string('isbn13', 20)->change()->nullable();
            $table->string('isbn10', 20)->change()->nullable();
            $table->string('language', 20)->change()->nullable();
            $table->integer('number_of_pages')->change()->nullable()->default(0);
            $table->date('publication_date')->nullable()->change();
            $table->longText('picture_data')->nullable()->change();
            $table->string('image_type', 20)->nullable()->change();
            $table->string('image_name', 191)->nullable()->change();
            $table->string('edition', 191)->nullable()->change();
            $table->double('price', 10, 2)->change()->nullable();
            $table->integer('quantity')->default(0)->change()->nullable();
            $table->integer('remaining_qty_for_section')->default(0)->change()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('amazon_book_informations', function (Blueprint $table) {
            //
        });
    }
};
