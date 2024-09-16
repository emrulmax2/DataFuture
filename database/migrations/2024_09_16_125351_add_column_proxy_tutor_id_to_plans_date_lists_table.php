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
            $table->unsignedBigInteger('proxy_tutor_id')->nullable()->after('canceled_reason');
            $table->unsignedBigInteger('proxy_assigned_by')->nullable()->after('proxy_tutor_id');
            $table->dateTime('proxy_assigned_at')->nullable()->after('proxy_assigned_by');
            $table->text('proxy_class_tutor_note')->nullable()->after('proxy_assigned_at');
            $table->foreign('proxy_tutor_id')->references('id')->on('users')->onDelete('set null')->onUpdate('set null');
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
