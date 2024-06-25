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
        Schema::table('comon_smtps', function (Blueprint $table) {
            $table->string('smtp_email_password', 80)->nullable()->after('smtp_pass');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comon_smtps', function (Blueprint $table) {
            $table->dropColumn('smtp_email_password');
        });
    }
};
