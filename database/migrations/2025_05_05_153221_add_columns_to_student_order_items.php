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
        Schema::table('student_order_items', function (Blueprint $table) {
            $table->integer('number_of_free')->nullable()->after('quantity')
                ->comment('number of the free items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_order_items', function (Blueprint $table) {
            // Drop the 'number_of_free' column
            $table->dropColumn('number_of_free');
        });
    }
};
