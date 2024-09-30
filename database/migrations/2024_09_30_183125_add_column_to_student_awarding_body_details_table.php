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
        Schema::table('student_awarding_body_details', function (Blueprint $table) {

            $table->text('remarks')->nullable()->after('registration_document_verified');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_awarding_body_details', function (Blueprint $table) {
            //
        });
    }
};
