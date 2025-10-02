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
            
            $table->softDeletes()->after('updated_at');
            $table->string('created_by')->nullable()->after('availability');
            $table->string('updated_by')->nullable()->after('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issue_types', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['created_by', 'updated_by']);
        });
    }
};
