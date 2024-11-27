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
        Schema::table('employees', function (Blueprint $table) {
            $table->smallInteger('can_access_all')->default(0)->nullable()->after('status')->comment('0 = No, 1 = Yes');
            $table->smallInteger('locked_profile')->default(0)->nullable()->after('can_access_all')->comment('0 = No, 1 = Yes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['can_access_all', 'locked_profile']);
        });
    }
};
