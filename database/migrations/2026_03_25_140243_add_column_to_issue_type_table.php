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
        Schema::table('issue_types', function (Blueprint $table) {
            //change availability column to string and add default value as Student
            $table->string('reporting_email')->after('availability')->nullable()->comment('Email address to which the issue will be reported. If null, it will be reported to the default email address configured in the system.'); 
            // Change enum values via raw SQL
            DB::statement("ALTER TABLE issue_types MODIFY availability ENUM('Student','Employee','Both') NOT NULL DEFAULT 'Employee' COMMENT 'Student, Employee, Both'");   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issue_types', function (Blueprint $table) {
            //revert availability column to previous state if needed
    
            $table->dropColumn('reporting_email');
            DB::statement("ALTER TABLE issue_types MODIFY availability ENUM('Student','Employee') NOT NULL DEFAULT 'Employee' COMMENT 'Student, Employee'");
  
        });
    }
};
