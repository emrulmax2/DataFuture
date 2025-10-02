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
        Schema::table('issue_types', function (Blueprint $table) {
            
            //add a ComonSmtp id as foreign key
            $table->unsignedBigInteger('comon_smtp_id')->nullable()->after('id');
            $table->foreign('comon_smtp_id')->references('id')->on('comon_smtps')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issue_types', function (Blueprint $table) {
            $table->dropForeign(['comon_smtp_id']);
            $table->dropColumn('comon_smtp_id');
        });
    }
};
