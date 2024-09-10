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
            $table->enum('status', ['Schedule', 'Ongoing', 'Held', 'Canceled', 'Unknown'])->default('Schedule')->after('feed_given')->nullable();
            $table->text('canceled_reason')->after('status')->nullable();
            $table->unsignedBigInteger('canceled_by')->after('canceled_reason')->nullable();
            $table->datetime('canceled_at')->after('canceled_by')->nullable();
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
