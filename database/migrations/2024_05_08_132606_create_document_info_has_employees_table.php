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
        Schema::create('document_info_has_employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_info_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('document_role_and_permission_id');

            $table->foreign('document_role_and_permission_id', 'dfhrp_pk_dihe_id')->references('id')->on('document_role_and_permissions')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('document_info_id')->references('id')->on('document_infos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_info_has_employees');
    }
};
