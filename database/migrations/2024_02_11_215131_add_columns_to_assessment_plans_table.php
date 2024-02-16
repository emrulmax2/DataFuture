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
        Schema::table('assessment_plans', function (Blueprint $table) {
            $table->timestamp('visible_at')->nullable()->after('published_at'); 
            $table->timestamp('resubmission_at')->nullable()->after('visible_at');
            $table->timestamp('resubmission_visible_at')->nullable()->after('resubmission_at');
            $table->unsignedBigInteger('created_by')->nullable()->after('resubmission_visible_at');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assessment_plans', function (Blueprint $table) {
            $table->dropColumn('visible_at');
            $table->dropColumn('resubmission_at');
            $table->dropColumn('resubmission_visible_at');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });
    }
};
