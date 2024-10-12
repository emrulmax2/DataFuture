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
        Schema::table('exam_result_prev', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_at');
            $table->timestamp('updated_at')->nullable()->after('updated_by');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_result_prev', function (Blueprint $table) {
            //
        });
    }
};
