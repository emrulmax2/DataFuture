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
            $table->dropColumn(['term_declaration_name', 'session_term_name']);
            $table->string('proforma_no')->nullable()->after('id');
            $table->smallInteger('status')->default('1')->comment('1=Proforma,2=Invoiced')->after('claim_amount');
            $table->dateTime('invoiced_at')->nullable()->after('status');
            $table->unsignedBigInteger('invoiced_by')->nullable()->after('invoiced_at');
            $table->foreign('invoiced_by')->references('id')->on('users')->onDelete('set null');
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
