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
        Schema::table('student_shopping_carts', function (Blueprint $table) {
            //quantity
            $table->integer('quantity')->default(1)->nullable()->after('term_declaration_id')
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
        Schema::table('student_shopping_carts', function (Blueprint $table) {
            //
            $table->dropColumn('quantity');
        });
    }
};
