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
        Schema::table('student_orders', function (Blueprint $table) {
            $table->string('invoice_number')->after('id')
                ->nullable()
                ->comment('Invoice number of the order')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_orders', function (Blueprint $table) {
            $table->dropIndex(['invoice_number']); // Drop the index when rolling back
            $table->dropColumn('invoice_number'); // Drop the column when rolling back
        });
    }
};
