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
        Schema::create('student_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete()
                ->comment('Foreign key to the students table');
            $table->foreignId('student_order_id')
                ->constrained('student_orders')
                ->cascadeOnDelete()
                ->comment('Foreign key to the student_orders table');
            $table->foreignId('letter_set_id')
                ->constrained('letter_sets')
                ->cascadeOnDelete()
                ->comment('Foreign key to the letter_sets table');

            $table->double('sub_amount', 8, 2)->default(0)->nullable()
                ->comment('Subtotal amount of the order item');
            $table->double('tax_amount', 8, 2)->default(0)->nullable()
                ->comment('Tax amount of the order item');
            $table->double('total_amount', 8, 2)->default(0)->nullable()
                ->comment('Total amount of the order item');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_order_items');
    }
};
