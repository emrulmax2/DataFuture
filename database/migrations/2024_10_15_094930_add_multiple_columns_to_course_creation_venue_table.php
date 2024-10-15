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
        Schema::table('course_creation_venue', function (Blueprint $table) {
            $table->smallInteger('evening_and_weekend')->nullable()->comment('0 = Weekdays, 1 = Weekends')->after('slc_code');
            $table->integer('weekdays')->nullable()->after('evening_and_weekend');
            $table->integer('weekends')->nullable()->after('weekdays');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_creation_venue', function (Blueprint $table) {
            $table->dropColumn(['evening_and_weekend', 'weekdays', 'weekends']);
        });
    }
};
