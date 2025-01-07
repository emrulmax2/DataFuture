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
        Schema::table('module_datafutures', function (Blueprint $table) {
            $table->dropColumn(['field_name', 'field_type', 'field_desc']);
            $table->unsignedBigInteger('datafuture_field_id')->after('course_module_id');
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('module_datafutures', function (Blueprint $table) {
            //
        });
    }
};
