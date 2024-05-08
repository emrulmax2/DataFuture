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
        Schema::create('document_role_and_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('display_name', 145);
            $table->string('type', 145);
            $table->smallInteger('create')->default(0);
            $table->smallInteger('read')->default(0);
            $table->smallInteger('update')->default(0);
            $table->smallInteger('delete')->default(0);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_role_and_permissions');
    }
};
