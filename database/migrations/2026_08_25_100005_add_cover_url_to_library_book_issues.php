<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Snapshot the book's cover alongside its title and author.
 *
 * The desk lists issues, not catalogue entries, so it has no title id to look
 * up per row — fetching a cover for each would be one API call per line. The
 * cover is captured with the rest of the book details when the copy is taken,
 * for the same reason: the row has to render years later whether or not
 * Operations is reachable or the title still exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('library_book_issues', function (Blueprint $table) {
            $table->string('cover_url', 2048)->nullable()->after('isbn13');
        });
    }

    public function down(): void
    {
        Schema::table('library_book_issues', function (Blueprint $table) {
            $table->dropColumn('cover_url');
        });
    }
};
