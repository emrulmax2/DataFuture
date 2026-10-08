<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Where the file actually sits.
 *
 * `path` holds a URL for showing the file and `disk_type` which disk it is on,
 * but reading it back meant rebuilding the key by convention — which breaks the
 * moment a file lands on a different disk, as it does when S3 refuses and the
 * upload falls back to local storage. The key is now recorded.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_course_change_request_documents', function (Blueprint $table) {
            $table->string('storage_key', 512)->nullable()->after('path');
        });
    }

    public function down(): void
    {
        Schema::table('student_course_change_request_documents', function (Blueprint $table) {
            $table->dropColumn('storage_key');
        });
    }
};
