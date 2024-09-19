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
        Schema::table('plans_date_lists', function (Blueprint $table) {

            $table->enum('class_file_upload_found', ['Yes', 'No', 'Undecided'])->default("Undecided")->after('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans_date_lists', function (Blueprint $table) {
            //
        });
    }
};
