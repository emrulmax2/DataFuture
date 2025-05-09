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
            $table->unsignedBigInteger('term_declaration_id')
                ->after('student_order_id') // Add after the 'student_order_id' column
                ->nullable()
                ->comment('Foreign key to the term_declarations table');
            $table->integer('quantity')->default(1)->nullable()->after('letter_set_id')
                ->comment('number of the quantity');

            $table->enum('product_type', ['Free', 'Paid'])
                ->default('Paid')->after('quantity')
                ->comment('Type of the product in the shopping cart');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_order_items', function (Blueprint $table) {
            // Drop the 'term_declaration_id' column
            $table->dropColumn('term_declaration_id');

            // Drop the 'quantity' column
            $table->dropColumn('quantity');

            // Drop the 'product_type' column
            $table->dropColumn('product_type');
        });
    }
};
