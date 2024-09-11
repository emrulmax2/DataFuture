<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('plans_date_lists', function (Blueprint $table) {
            DB::statement("ALTER TABLE plans_date_lists CHANGE COLUMN `status` `status` ENUM('Schedule', 'Scheduled','Ongoing','Held','Completed','Canceled','Unknown') NULL DEFAULT 'Scheduled'");
            DB::statement("UPDATE `plans_date_lists` SET status='Scheduled' WHERE status = 'Schedule'");
            DB::statement("UPDATE `plans_date_lists` SET status='Completed' WHERE status = 'Held'");
            DB::statement("ALTER TABLE plans_date_lists CHANGE COLUMN `status` `status` ENUM('Scheduled','Ongoing','Completed','Canceled','Unknown') NULL DEFAULT 'Scheduled'");
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
