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
        Schema::create('university_payment_claim_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('university_payment_claim_id')->constrained('university_payment_claims', 'id', 'upci_fk')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('slc_installment_id')->nullable()->constrained('slc_installments', 'id', 'sii_fk')->nullOnDelete();
            $table->smallInteger('status')->default(1)->comment('1=Claimed,2=Received,3=Cancelled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('university_payment_claim_students');
    }
};
