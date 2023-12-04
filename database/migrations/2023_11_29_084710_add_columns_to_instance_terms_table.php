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
        Schema::table('instance_terms', function (Blueprint $table) {
            $table->unsignedBigInteger('term_type_id')->nullable()->after('term');
            $table->unsignedBigInteger('term_declaration_id')->nullable()->after('name');
            $table->foreign('term_type_id')->references('id')->on('term_types')->onDelete('set null')->onUpdate('set null');
            $table->foreign('term_declaration_id')->references('id')->on('term_declarations')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('instance_terms', function (Blueprint $table) {
            $table->dropColumn('term_type_id');
            $table->dropColumn('term_declaration_id');
        });
    }
};
