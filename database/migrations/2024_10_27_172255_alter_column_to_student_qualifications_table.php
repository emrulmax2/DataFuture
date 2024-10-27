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
        Schema::table('student_qualifications', function (Blueprint $table) {
            $table->string('awarding_body',145)->nullable()->change();
            $table->string('highest_academic',145)->nullable()->change();
            $table->string('subjects',145)->nullable()->change();
            $table->string('result',145)->nullable()->change();
            $table->date('degree_award_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_qualifications', function (Blueprint $table) {
            //
        });
    }
};
