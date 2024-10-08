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
        Schema::table('plans_date_lists', function (Blueprint $table) {
            $table->text('proxy_reason')->nullable()->after('proxy_assigned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans_date_lists', function (Blueprint $table) {
            //
        });
    }
};
