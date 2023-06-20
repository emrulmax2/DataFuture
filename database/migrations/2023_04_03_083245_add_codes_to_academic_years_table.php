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
        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropColumn('code');
            $table->enum('is_hesa', ['0', '1'])->default('0')->after('name');
            $table->string('hesa_code')->nullable()->after('is_hesa');
            $table->enum('is_df', ['0', '1'])->default('0')->after('hesa_code');
            $table->string('df_code')->nullable()->after('is_df');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->string('code');
            $table->dropColumn('is_hesa');
            $table->dropColumn('hesa_code');
            $table->dropColumn('is_df');
            $table->dropColumn('df_code');
        });
    }
};
