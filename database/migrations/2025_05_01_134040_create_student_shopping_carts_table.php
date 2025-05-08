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
        Schema::create('student_shopping_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete()
                ->comment('Foreign key to the students table');
            $table->foreignId('letter_set_id')
                ->constrained('letter_sets')
                ->cascadeOnDelete()
                ->comment('Foreign key to the letter_sets table');
            $table->foreignId('term_declaration_id')
                ->constrained('term_declarations')
                ->cascadeOnDelete()
                ->comment('Foreign key to the term_declarations table');

            $table->timestamp('expire_at')->nullable()
                ->comment('Expiration date of the shopping cart');

            $table->double('sub_amount', 8, 2)->default(0)->nullable();
            $table->double('tax_amount', 8, 2)->default(0)->nullable();
            $table->double('total_amount', 8, 2)->default(0)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_shopping_carts');
    }
};
