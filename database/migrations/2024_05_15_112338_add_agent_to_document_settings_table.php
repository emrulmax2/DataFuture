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
        Schema::table('document_settings', function (Blueprint $table) {
            $table->enum('agent',[0,1])->default(0)->after('staff');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_settings', function (Blueprint $table) {
            $table->dropColumn('agent');
        });
    }
};
