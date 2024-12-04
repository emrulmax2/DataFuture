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
        Schema::table('result_comparisons', function (Blueprint $table) {
            
            $table->enum('publish_done',["Yes","No"])->nullable()->after('result_Ids');
            $table->enum('republish_done',["Yes","No"])->nullable()->after('publish_done');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('result_comparisons', function (Blueprint $table) {
                
                $table->dropColumn('publish_done');
                $table->dropColumn('republish_done');
        });
    }
};
