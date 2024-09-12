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
        Schema::table('slc_coc_documents', function (Blueprint $table) {
            $table->dropColumn('student_document_id');
            $table->smallInteger('hard_copy_check')->default(0)->nullable()->after('slc_coc_id');
            $table->string('doc_type', 191)->nullable()->after('hard_copy_check');
            $table->string('disk_type', 191)->nullable()->after('doc_type');
            $table->text('path')->nullable()->after('disk_type');
            $table->text('display_file_name')->nullable()->after('path');
            $table->text('current_file_name')->nullable()->after('display_file_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slc_coc_documents', function (Blueprint $table) {
            //
        });
    }
};
