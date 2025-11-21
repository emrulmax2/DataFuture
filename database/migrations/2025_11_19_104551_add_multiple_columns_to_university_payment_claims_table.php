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
        Schema::table('university_payment_claims', function (Blueprint $table) {
            $table->foreignId('term_declaration_id')->nullable()->constrained('term_declarations', 'id', 'tdec_fk')->nullOnDelete();
            $table->smallInteger('session_term')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('university_payment_claims', function (Blueprint $table) {
            //
        });
    }
};
