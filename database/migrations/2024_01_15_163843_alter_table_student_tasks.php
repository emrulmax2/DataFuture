<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_tasks', function (Blueprint $table) {
            DB::statement("ALTER TABLE student_tasks CHANGE COLUMN `status` `status` ENUM('Pending', 'In Progress', 'Completed', 'Canceled') NOT NULL DEFAULT 'Pending'");
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
